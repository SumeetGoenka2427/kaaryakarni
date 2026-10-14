<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentPurpose;
use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Services\Audit\AuditLogger;
use App\Services\Receipts\ReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Admin → Receipts (admin/receipts.html): every issued receipt, downloadable as PDF. */
class ReceiptAdminController extends Controller
{
    public function index(Request $request): View
    {
        $purpose = PaymentPurpose::tryFrom((string) $request->query('purpose'));
        $q = trim((string) $request->query('q', ''));

        $receipts = Receipt::query()
            ->with('payment.member:id,name,membership_no', 'payment.payable')
            ->when($purpose, fn ($query) => $query->whereHas('payment', fn ($p) => $p->where('purpose', $purpose)))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('receipt_no', 'like', "%{$q}%")
                ->orWhereHas('payment', fn ($p) => $p->where('payer_name', 'like', "%{$q}%")->orWhere('payer_mobile', 'like', "%{$q}%"))))
            ->latest('id')->paginate(25)->withQueryString();

        return view('admin.receipts.index', compact('receipts', 'purpose', 'q'));
    }

    public function download(Receipt $receipt, ReceiptService $service): Response
    {
        return $service->download($receipt);
    }

    /** Rebuild the PDF (e.g. after the receipt logo or organisation details change). */
    public function regenerate(Receipt $receipt, ReceiptService $service, AuditLogger $audit): RedirectResponse
    {
        $service->regenerate($receipt);
        $audit->record('receipt.regenerated', $receipt, "Regenerated PDF for {$receipt->receipt_no}");

        return back()->with('success', "Receipt {$receipt->receipt_no} will be regenerated on next download.");
    }
}
