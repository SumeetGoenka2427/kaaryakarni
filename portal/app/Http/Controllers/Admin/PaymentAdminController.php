<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMode;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use App\Services\Payments\PaymentService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Admin → Payments (admin/payments.html): all online + offline payments, offline verification. */
class PaymentAdminController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        return view('admin.payments.index', [
            'payments' => $this->filtered($request)->with(['member:id,ulid,name', 'receipt:id,payment_id,receipt_no'])->latest('id')->paginate(25)->withQueryString(),
            'filters' => $request->only(['q', 'mode', 'status', 'purpose', 'from', 'to']),
            'submittedCount' => Payment::where('status', PaymentStatus::Submitted)->count(),
        ]);
    }

    public function show(Payment $payment): View
    {
        $payment->load(['member', 'payable', 'receipt', 'verifier:id,name', 'events' => fn ($q) => $q->latest('id')]);

        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Payment $payment): RedirectResponse
    {
        try {
            $this->payments->verifyOffline($payment);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment verified. A receipt has been issued and the payer notified.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];

        try {
            $this->payments->rejectOffline($payment, $reason);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment rejected. The payer has been notified.');
    }

    public function proof(Payment $payment): Response
    {
        return PaymentService::proofResponse($payment);
    }

    public function export(Request $request, AuditLogger $audit): StreamedResponse
    {
        $audit->record('payments.exported', null, 'Exported payments CSV', [], $request->only(['q', 'mode', 'status', 'purpose', 'from', 'to']));

        return response()->streamDownload(function () use ($request) {
            $writer = SimpleExcelWriter::create('php://output', 'csv');
            $this->filtered($request)->with(['member:id,name,membership_no', 'receipt:id,payment_id,receipt_no'])->orderBy('id')->lazy(500)
                ->each(fn (Payment $p) => $writer->addRow([
                    'Payment ID' => $p->ulid,
                    'Date' => ($p->paid_at ?? $p->created_at)->format('d-m-Y H:i'),
                    'Name' => $p->displayName(),
                    'Mobile' => $p->payer_mobile,
                    'Membership ID' => $p->member?->membership_no,
                    'Purpose' => $p->purpose->label(),
                    'Amount' => (float) $p->amount,
                    'Mode' => $p->mode->label(),
                    'Method' => $p->offline_method?->label() ?? ucfirst($p->provider),
                    'Reference' => $p->reference(),
                    'Status' => $p->status->label(),
                    'Receipt No' => $p->receipt?->receipt_no,
                ]));
            $writer->close();
        }, 'payments-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        $q = trim((string) $request->query('q', ''));

        return Payment::query()
            ->where('status', '!=', PaymentStatus::Created)
            ->when(PaymentMode::tryFrom((string) $request->query('mode')), fn ($query, $mode) => $query->where('mode', $mode))
            ->when(PaymentStatus::tryFrom((string) $request->query('status')), fn ($query, $status) => $query->where('status', $status))
            ->when(PaymentPurpose::tryFrom((string) $request->query('purpose')), fn ($query, $purpose) => $query->where('purpose', $purpose))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('payer_name', 'like', "%{$q}%")
                ->orWhere('payer_mobile', 'like', "%{$q}%")->orWhere('offline_reference', 'like', "%{$q}%")
                ->orWhere('provider_payment_id', 'like', "%{$q}%")->orWhere('ulid', $q)))
            ->when($request->date('from'), fn ($query, $from) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($query, $to) => $query->where('created_at', '<=', $to->endOfDay()));
    }
}
