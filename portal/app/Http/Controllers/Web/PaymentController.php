<?php

namespace App\Http\Controllers\Web;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Online checkout (gateway widget) and the checkout callback.
 * The callback only confirms a payment after verifying the gateway's HMAC
 * signature server-side; the webhook (RazorpayWebhookController) confirms
 * it independently, so a closed browser never loses a payment.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly PaymentGateway $gateway,
    ) {}

    public function checkout(Request $request, Payment $payment): View|RedirectResponse
    {
        $this->authorizeOwner($request, $payment);

        if ($payment->isComplete() || $payment->status !== PaymentStatus::Pending) {
            return $this->finish($payment);
        }

        return view('web.payments.checkout', [
            'payment' => $payment,
            'checkout' => [
                'key' => config('portal.payments.razorpay.key_id'),
                'amount' => (int) round((float) $payment->amount * 100),
                'currency' => $payment->currency,
                'order_id' => $payment->provider_order_id,
                'name' => setting('org_name_en'),
                'description' => $payment->payable?->paymentDescription() ?? $payment->purpose->label(),
                'prefill' => ['name' => $payment->payer_name, 'contact' => $payment->payer_mobile, 'email' => $payment->payer_email],
            ],
        ]);
    }

    public function callback(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizeOwner($request, $payment);

        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:100'],
            'razorpay_order_id' => ['required', 'string', 'max:100'],
            'razorpay_signature' => ['required', 'string', 'max:200'],
        ]);

        $valid = $data['razorpay_order_id'] === $payment->provider_order_id
            && $this->gateway->verifyCheckout($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);

        PaymentEvent::create([
            'payment_id' => $payment->id, 'provider' => $this->gateway->name(), 'event' => 'checkout.callback',
            'signature_valid' => $valid, 'payload' => ['payment_id' => $data['razorpay_payment_id'], 'order_id' => $data['razorpay_order_id']],
        ]);

        if ($valid) {
            try {
                $payment = $this->payments->confirmOnline($payment, $data['razorpay_payment_id'], 'callback');
            } catch (DomainException) {
                // fall through to the "could not confirm" message
            }
        }

        return $this->finish($payment->fresh());
    }

    /** Widget closed without paying. */
    public function cancel(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizeOwner($request, $payment);

        if ($payment->status === PaymentStatus::Pending) {
            $this->payments->failOnline($payment, 'Checkout closed by payer');
        }

        return $this->finish($payment->fresh());
    }

    private function finish(Payment $payment): RedirectResponse
    {
        $target = localized_route($payment->meta['return'] ?? 'home', $payment->meta['return_params'] ?? []);

        return match (true) {
            $payment->isComplete() => redirect($target)->with('success', __('web.payments.success')),
            $payment->status === PaymentStatus::Failed => redirect($target)->with('warning', __('web.payments.cancelled')),
            default => redirect($target)->with('warning', __('web.payments.failed')),
        };
    }

    /** Only the member who owns the payment (or the guest session that started it) may use it. */
    private function authorizeOwner(Request $request, Payment $payment): void
    {
        $member = $request->user('member');
        $ownsAsMember = $member && $payment->member_id === $member->id;
        $ownsAsGuest = in_array($payment->ulid, (array) $request->session()->get('guest_payments', []), true);

        abort_unless($ownsAsMember || $ownsAsGuest, 404);
    }
}
