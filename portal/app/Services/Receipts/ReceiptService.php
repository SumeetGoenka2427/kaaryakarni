<?php

namespace App\Services\Receipts;

use App\Models\Payment;
use App\Models\Receipt;
use App\Services\Branding\BrandingService;
use App\Services\Support\NumberSequence;
use App\Support\AmountInWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receipts for completed payments: gap-free numbers per Indian financial
 * year (KSJ/26-27/000001) and English PDFs (dompdf). The PDF is generated
 * once, kept on the private disk and only downloadable after an
 * authorization check in the controller.
 */
class ReceiptService
{
    public function __construct(private readonly NumberSequence $sequence) {}

    /** Issue a receipt for a completed payment (idempotent). */
    public function issue(Payment $payment): Receipt
    {
        if ($existing = Receipt::where('payment_id', $payment->id)->first()) {
            return $existing;
        }

        $fy = NumberSequence::financialYear($payment->paid_at ?? now());
        $number = sprintf('KSJ/%s/%06d', $fy, $this->sequence->next("receipt-{$fy}"));

        return Receipt::create(['payment_id' => $payment->id, 'receipt_no' => $number, 'issued_at' => now()]);
    }

    public function download(Receipt $receipt): Response
    {
        $disk = Storage::disk('private');

        if (! $receipt->pdf_path || ! $disk->exists($receipt->pdf_path)) {
            $path = 'receipts/'.str_replace('/', '-', $receipt->receipt_no).'.pdf';
            $disk->put($path, $this->render($receipt));
            $receipt->pdf_path = $path;
            $receipt->save();
        }

        return response($disk->get($receipt->pdf_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Receipt-'.str_replace('/', '-', $receipt->receipt_no).'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /** Forget the stored PDF (e.g. after a logo change) so it is regenerated. */
    public function regenerate(Receipt $receipt): void
    {
        if ($receipt->pdf_path) {
            Storage::disk('private')->delete($receipt->pdf_path);
        }
        $receipt->pdf_path = null;
        $receipt->save();
    }

    public function render(Receipt $receipt): string
    {
        $payment = $receipt->payment->load(['member', 'payable']);
        $logo = app(BrandingService::class)->get('logo_receipt') ?? app(BrandingService::class)->get('logo_header');
        $logoData = null;
        if ($logo && Storage::disk('public')->exists($logo->path)) {
            $mime = str_ends_with($logo->path, '.png') ? 'image/png' : 'image/webp';
            $logoData = $mime === 'image/png' ? 'data:image/png;base64,'.base64_encode(Storage::disk('public')->get($logo->path)) : null;
        }

        return Pdf::loadView('pdf.receipt', [
            'receipt' => $receipt,
            'payment' => $payment,
            'purpose' => $payment->payable?->paymentDescription() ?? $payment->purpose->label(),
            'amountWords' => AmountInWords::rupees($payment->amount),
            'logo' => $logoData,
        ])->setOption(['isFontSubsettingEnabled' => true, 'defaultFont' => 'DejaVu Sans'])->setPaper('a5', 'landscape')->output();
    }
}
