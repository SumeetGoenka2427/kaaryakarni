<?php

namespace App\Services\Donations;

use App\Enums\DonationStatus;
use App\Enums\PaymentPurpose;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Member;
use App\Services\Audit\AuditLogger;
use App\Services\Content\PublicContent;
use App\Services\Payments\PaymentService;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Donations and event tickets for any campaign, by members or guests,
 * online (gateway) or offline (admin-verified). Totals move only via the
 * Donation payment hooks or a refund here.
 */
class DonationService
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array{type: string, amount?: float, quantity?: int, donor_name: string, donor_mobile: string, donor_email?: ?string, donor_pan?: ?string, is_anonymous?: bool, message?: ?string}  $data
     * @return array{0: Donation, 1: ?array} donation + checkout data (online only)
     */
    public function donate(DonationCampaign $campaign, array $data, string $mode, ?Member $member, array $offline = [], ?UploadedFile $proof = null): array
    {
        if (! $campaign->isOpen()) {
            throw new DomainException(__('web.donations.closed'));
        }
        if (($mode === 'online' && ! $campaign->allow_online) || ($mode === 'offline' && ! $campaign->allow_offline)) {
            throw new DomainException(__('web.donations.mode_not_allowed'));
        }

        $isTicket = $data['type'] === 'ticket';
        if ($isTicket && ! $campaign->sellsTickets()) {
            throw new DomainException(__('web.donations.no_tickets'));
        }
        $quantity = $isTicket ? max(1, (int) ($data['quantity'] ?? 1)) : 1;
        $amount = $isTicket ? round((float) $campaign->ticket_price * $quantity, 2) : round((float) $data['amount'], 2);

        $donation = new Donation([
            'donor_name' => $data['donor_name'],
            'donor_mobile' => $data['donor_mobile'],
            'donor_email' => $data['donor_email'] ?? null,
            'donor_pan' => isset($data['donor_pan']) ? strtoupper($data['donor_pan']) : null,
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
            'message' => $data['message'] ?? null,
            'locale' => app()->getLocale(),
        ]);
        $donation->campaign_id = $campaign->id;
        $donation->member_id = $member?->id;
        $donation->type = $isTicket ? 'ticket' : 'donation';
        $donation->quantity = $quantity;
        $donation->amount = $amount;
        $donation->status = DonationStatus::Pending;
        $donation->save();

        $purpose = $isTicket ? PaymentPurpose::Ticket : PaymentPurpose::Donation;
        $payer = ['name' => $donation->donor_name, 'mobile' => $donation->donor_mobile, 'email' => $donation->donor_email];

        if ($mode === 'online') {
            [$payment, $checkout] = $this->payments->startOnline($donation, $purpose, $amount, $member, $payer);
            $donation->payment_id = $payment->id;
            $donation->save();

            return [$donation, $checkout];
        }

        $this->payments->submitOffline($donation, $purpose, $amount, $member, $offline, $proof, $payer);

        return [$donation->fresh(), null];
    }

    /**
     * Record a refund / cancellation of a completed donation (money returned
     * outside the system, e.g. from the Razorpay dashboard or by cheque).
     * Campaign totals are reduced in the same transaction.
     */
    public function refund(Donation $donation, string $reason): void
    {
        DB::transaction(function () use ($donation, $reason) {
            $donation = Donation::whereKey($donation->id)->lockForUpdate()->firstOrFail();

            if (! in_array($donation->status, [DonationStatus::Successful, DonationStatus::Verified], true)) {
                throw new DomainException('Only successful or verified donations can be refunded.');
            }

            if ($donation->counted) {
                DB::table('donation_campaigns')->where('id', $donation->campaign_id)->update([
                    'raised_amount' => DB::raw('GREATEST(raised_amount - '.(float) $donation->amount.', 0)'),
                    'donor_count' => DB::raw('GREATEST(CAST(donor_count AS SIGNED) - 1, 0)'),
                ]);
                $donation->counted = false;
            }
            $donation->status = DonationStatus::Refunded;
            $donation->save();

            if ($donation->payment) {
                $this->payments->markRefunded($donation->payment, $reason);
            }
        });

        PublicContent::flush();
        $this->audit->record('donation.refunded', $donation, "Refunded ₹{$donation->amount} from {$donation->donor_name}: {$reason}");
    }

    /** Rebuild every campaign's totals from confirmed donations. Returns the number of campaigns fixed. */
    public function recalculate(): int
    {
        $fixed = 0;

        DonationCampaign::withTrashed()->each(function (DonationCampaign $campaign) use (&$fixed) {
            $row = Donation::where('campaign_id', $campaign->id)
                ->whereIn('status', [DonationStatus::Successful, DonationStatus::Verified])
                ->selectRaw('COALESCE(SUM(amount), 0) total, COUNT(*) donors')->first();

            if ((float) $campaign->raised_amount !== (float) $row->total || $campaign->donor_count !== (int) $row->donors) {
                DB::table('donation_campaigns')->where('id', $campaign->id)->update(['raised_amount' => $row->total, 'donor_count' => $row->donors]);
                $fixed++;
            }
        });

        Donation::whereIn('status', [DonationStatus::Successful, DonationStatus::Verified])->where('counted', false)->update(['counted' => true]);
        PublicContent::flush();

        return $fixed;
    }
}
