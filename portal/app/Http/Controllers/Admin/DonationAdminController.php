<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DonationStatus;
use App\Enums\PaymentMode;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Services\Audit\AuditLogger;
use App\Services\Donations\DonationService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin → Donations → Records: every online and offline donation.
 * Offline verification happens on the payment (Payments → Review), so the
 * same rules and receipts apply to memberships and donations.
 */
class DonationAdminController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.donations.index', [
            'donations' => $this->filtered($request)->with(['campaign:id,title_en', 'payment:id,ulid,mode,offline_method,offline_reference,provider_payment_id'])
                ->latest('id')->paginate(25)->withQueryString(),
            'campaigns' => DonationCampaign::withTrashed()->orderBy('title_en')->pluck('title_en', 'id'),
            'filters' => $request->only(['q', 'campaign', 'mode', 'status', 'from', 'to']),
            'totals' => Donation::whereIn('status', [DonationStatus::Successful, DonationStatus::Verified])
                ->selectRaw('COUNT(*) n, COALESCE(SUM(amount),0) total')->first(),
        ]);
    }

    public function refund(Request $request, Donation $donation, DonationService $donations): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']])['reason'];

        try {
            $donations->refund($donation, $reason);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Donation marked as refunded. Campaign totals have been updated.');
    }

    public function export(Request $request, AuditLogger $audit): StreamedResponse
    {
        $audit->record('donations.exported', null, 'Exported donations CSV', [], $request->only(['q', 'campaign', 'mode', 'status', 'from', 'to']));

        return response()->streamDownload(function () use ($request) {
            $writer = SimpleExcelWriter::create('php://output', 'csv');
            $this->filtered($request)->with(['campaign:id,title_en', 'payment.receipt'])->orderBy('id')->lazy(500)
                ->each(fn (Donation $d) => $writer->addRow([
                    'Date' => $d->created_at->format('d-m-Y H:i'),
                    'Donor' => $d->donor_name,
                    'Mobile' => $d->donor_mobile,
                    'Email' => $d->donor_email,
                    'PAN' => $d->donor_pan,
                    'Campaign' => $d->campaign?->title_en,
                    'Type' => $d->type === 'ticket' ? "Ticket × {$d->quantity}" : 'Donation',
                    'Amount' => (float) $d->amount,
                    'Mode' => $d->payment?->mode->label(),
                    'Reference' => $d->payment?->reference(),
                    'Status' => $d->status->label(),
                    'Receipt No' => $d->payment?->receipt?->receipt_no,
                    'Anonymous' => $d->is_anonymous ? 'Yes' : 'No',
                ]));
            $writer->close();
        }, 'donations-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        $q = trim((string) $request->query('q', ''));
        $mode = PaymentMode::tryFrom((string) $request->query('mode'));

        return Donation::query()
            ->when($request->integer('campaign'), fn ($query, $c) => $query->where('campaign_id', $c))
            ->when(DonationStatus::tryFrom((string) $request->query('status')), fn ($query, $s) => $query->where('status', $s))
            ->when($mode, fn ($query) => $query->whereHas('payment', fn ($p) => $p->where('mode', $mode)))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('donor_name', 'like', "%{$q}%")->orWhere('donor_mobile', 'like', "%{$q}%")))
            ->when($request->date('from'), fn ($query, $from) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($query, $to) => $query->where('created_at', '<=', $to->endOfDay()));
    }
}
