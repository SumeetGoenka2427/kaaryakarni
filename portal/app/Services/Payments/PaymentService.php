<?php

namespace App\Services\Payments;

use App\Contracts\Payable;
use App\Enums\OfflineMethod;
use App\Enums\PaymentMode;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\Member;
use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use App\Services\Receipts\ReceiptService;
use App\Services\Notifications\Notifier;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The only place a payment's status changes (CLAUDE.md §18).
 *
 * - Online: created → pending (gateway order) → successful | failed.
 *   "Successful" is set only after a server-side signature check of the
 *   checkout callback or a signed webhook — never from a browser redirect.
 *   Both paths are idempotent and re-check the amount.
 * - Offline: submitted → verified (admin) | cancelled (admin rejects).
 *
 * Every status change and its Payable side effect run in one transaction;
 * completed payments get a receipt in the same transaction.
 */
class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly ReceiptService $receipts,
        private readonly AuditLogger $audit,
        private readonly Notifier $notifier,
    ) {}

    public function onlineAvailable(): bool
    {
        return $this->gateway->isConfigured();
    }

    /** Start an online payment and return [payment, checkout data for the browser]. */
    public function startOnline(Model&Payable $payable, PaymentPurpose $purpose, float $amount, ?Member $member, array $payer = []): array
    {
        if (! $this->gateway->isConfigured()) {
            throw new DomainException(__('web.payments.online_unavailable'));
        }

        $payment = $this->newPayment($payable, $purpose, $amount, $member, PaymentMode::Online, $this->gateway->name(), $payer);
        $payment->status = PaymentStatus::Created;
        $payment->save();

        $order = $this->gateway->createOrder($payment);
        $payment->provider_order_id = $order['order_id'];
        $payment->status = PaymentStatus::Pending;
        $payment->save();

        return [$payment, $order['checkout']];
    }

    /** Record an offline payment (cash / cheque / UPI / bank transfer) for admin verification. */
    public function submitOffline(Model&Payable $payable, PaymentPurpose $purpose, float $amount, ?Member $member, array $details, ?UploadedFile $proof = null, array $payer = []): Payment
    {
        $payment = DB::transaction(function () use ($payable, $purpose, $amount, $member, $details, $proof, $payer) {
            $payment = $this->newPayment($payable, $purpose, $amount, $member, PaymentMode::Offline, 'offline', $payer);
            $payment->offline_method = OfflineMethod::from($details['offline_method']);
            $payment->offline_reference = $details['offline_reference'] ?? null;
            $payment->offline_paid_on = $details['offline_paid_on'] ?? null;
            $payment->status = PaymentStatus::Submitted;
            if ($proof) {
                $payment->proof = $this->storeProof($proof);
            }
            $payment->save();
            $payable->paymentSubmitted($payment);

            return $payment;
        });

        $this->notify($payment, 'payment_submitted');

        return $payment;
    }

    /**
     * Gateway says paid (verified checkout signature or signed webhook).
     * Idempotent: a payment already completed is returned unchanged.
     */
    public function confirmOnline(Payment $payment, string $providerPaymentId, string $source, ?int $amountPaise = null): Payment
    {
        $alreadyComplete = false;
        $amountMismatch = false;

        $payment = DB::transaction(function () use ($payment, $providerPaymentId, $source, $amountPaise, &$alreadyComplete, &$amountMismatch) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->isComplete()) {
                $alreadyComplete = true;

                return $payment;
            }
            if ($payment->mode !== PaymentMode::Online || $payment->isFinal()) {
                throw new DomainException('This payment cannot be confirmed.');
            }
            if ($amountPaise !== null && $amountPaise !== (int) round((float) $payment->amount * 100)) {
                // Mark failed and commit; the exception is thrown after the transaction
                // so the failure is not rolled back.
                $this->fail($payment, 'Amount mismatch from gateway');
                $amountMismatch = true;

                return $payment;
            }

            $payment->provider_payment_id = $providerPaymentId;
            $payment->status = PaymentStatus::Successful;
            $payment->paid_at = now();
            $payment->meta = array_merge($payment->meta ?? [], ['confirmed_via' => $source]);
            $payment->save();

            $payment->payable?->paymentSucceeded($payment);
            $this->receipts->issue($payment);

            return $payment;
        });

        if ($amountMismatch) {
            throw new DomainException('Payment amount does not match.');
        }

        if (! $alreadyComplete) {
            $this->notify($payment, 'payment_received');
        }

        return $payment;
    }

    public function failOnline(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if (! $payment->isFinal() && $payment->status !== PaymentStatus::Failed) {
                $this->fail($payment, $reason);
            }
        });
    }

    /** Admin confirms an offline payment was received. */
    public function verifyOffline(Payment $payment): Payment
    {
        $payment = DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->mode !== PaymentMode::Offline || $payment->status !== PaymentStatus::Submitted) {
                throw new DomainException('Only submitted offline payments can be verified.');
            }

            $payment->status = PaymentStatus::Verified;
            $payment->verified_by = auth('admin')->id();
            $payment->verified_at = now();
            $payment->paid_at = $payment->offline_paid_on?->startOfDay() ?? now();
            $payment->save();

            $payment->payable?->paymentSucceeded($payment);
            $this->receipts->issue($payment);

            return $payment;
        });

        $this->notify($payment, 'payment_received');
        $this->audit->record('payment.verified', $payment, "Verified offline payment ₹{$payment->amount} from {$payment->displayName()} ({$payment->offline_method?->label()} {$payment->offline_reference})");

        return $payment;
    }

    /** Admin rejects an offline payment (not received / wrong details). */
    public function rejectOffline(Payment $payment, string $reason): Payment
    {
        $payment = DB::transaction(function () use ($payment, $reason) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status !== PaymentStatus::Submitted) {
                throw new DomainException('Only submitted offline payments can be rejected.');
            }
            $payment->status = PaymentStatus::Cancelled;
            $payment->failure_reason = $reason;
            $payment->verified_by = auth('admin')->id();
            $payment->verified_at = now();
            $payment->save();
            $payment->payable?->paymentFailed($payment);

            return $payment;
        });

        $this->notify($payment, 'payment_rejected');
        $this->audit->record('payment.rejected', $payment, "Rejected offline payment ₹{$payment->amount} from {$payment->displayName()}: {$reason}");

        return $payment;
    }

    /** Record that a completed payment's money was returned (refund handled at the gateway / by cheque). */
    public function markRefunded(Payment $payment, string $reason): void
    {
        $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
        if (! $payment->isComplete()) {
            throw new DomainException('Only completed payments can be refunded.');
        }
        $payment->status = PaymentStatus::Refunded;
        $payment->failure_reason = Str::limit($reason, 490);
        $payment->meta = array_merge($payment->meta ?? [], ['refunded_at' => now()->toIso8601String(), 'refunded_by' => auth('admin')->id()]);
        $payment->save();
    }

    private function notify(Payment $payment, string $template): void
    {
        $payment->loadMissing(['member', 'receipt', 'payable']);
        $mobile = $payment->payer_mobile ?? $payment->member?->mobile;
        if (! $mobile) {
            return;
        }

        $this->notifier->mobile($mobile, $template, [
            'name' => explode(' ', (string) $payment->displayName())[0],
            'amount' => number_format((float) $payment->amount, 0),
            'purpose' => $payment->payable?->paymentDescription() ?? $payment->purpose->label(),
            'receipt_no' => $payment->receipt?->receipt_no ?? '',
        ], app()->getLocale(), $payment->member);
    }

    private function fail(Payment $payment, string $reason): void
    {
        $payment->status = PaymentStatus::Failed;
        $payment->failure_reason = Str::limit($reason, 490);
        $payment->save();
        $payment->payable?->paymentFailed($payment);
    }

    private function newPayment(Model&Payable $payable, PaymentPurpose $purpose, float $amount, ?Member $member, PaymentMode $mode, string $provider, array $payer): Payment
    {
        if ($amount <= 0) {
            throw new DomainException('Amount must be greater than zero.');
        }

        $payment = new Payment([
            'payer_name' => $payer['name'] ?? $member?->name,
            'payer_mobile' => $payer['mobile'] ?? $member?->mobile,
            'payer_email' => $payer['email'] ?? $member?->email,
        ]);
        $payment->member_id = $member?->id;
        $payment->purpose = $purpose;
        $payment->payable()->associate($payable);
        $payment->amount = round($amount, 2);
        $payment->currency = config('portal.payments.currency');
        $payment->mode = $mode;
        $payment->provider = $provider;

        return $payment;
    }

    /** Proof of offline payment: image (re-encoded) or PDF, private disk only. */
    private function storeProof(UploadedFile $file): array
    {
        if ($file->getMimeType() === 'application/pdf') {
            $path = $file->storeAs('payment-proofs', Str::lower(Str::ulid()).'.pdf', 'private');

            return ['disk' => 'private', 'path' => $path, 'type' => 'pdf'];
        }

        return app(ImageService::class)->store($file, 'payment-proofs', [1600], null, 'private') + ['type' => 'image'];
    }

    public static function proofRules(): array
    {
        return ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.config('portal.uploads.pdf_max_kb')];
    }

    public static function proofResponse(Payment $payment)
    {
        abort_unless($payment->proof && Storage::disk('private')->exists($payment->proof['path']), 404);

        return response(Storage::disk('private')->get($payment->proof['path']), 200, [
            'Content-Type' => ($payment->proof['type'] ?? 'image') === 'pdf' ? 'application/pdf' : 'image/webp',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
