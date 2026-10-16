<?php

namespace App\Services\Reports;

use App\Enums\BusinessStatus;
use App\Enums\DonationStatus;
use App\Enums\MatrimonialStatus;
use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\BusinessProfile;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\MatrimonialProfile;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

/** Admin dashboard figures from live data, aggregated in a few queries and cached briefly. */
class DashboardStats
{
    private const TTL = 60;

    public function counts(): array
    {
        return Cache::remember('admin.dashboard.counts', self::TTL, function () {
            $members = Member::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
            $businesses = BusinessProfile::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
            $offline = Payment::where('mode', PaymentMode::Offline)->where('status', PaymentStatus::Submitted)
                ->selectRaw('purpose, COUNT(*) c')->groupBy('purpose')->pluck('c', 'purpose');

            return [
                'members' => (int) $members->sum(),
                'lifetime' => Membership::where('status', MembershipStatus::Active)->count(),
                'members_pending' => (int) (($members[MemberStatus::UnderVerification->value] ?? 0) + ($members[MemberStatus::Pending->value] ?? 0)),
                'members_under_verification' => (int) ($members[MemberStatus::UnderVerification->value] ?? 0),
                'businesses_approved' => (int) ($businesses[BusinessStatus::Approved->value] ?? 0),
                'businesses_pending' => (int) ($businesses[BusinessStatus::Pending->value] ?? 0),
                'matrimonial_pending' => MatrimonialProfile::where('status', MatrimonialStatus::Pending)->count(),
                'donations_total' => (float) Donation::whereIn('status', [DonationStatus::Successful, DonationStatus::Verified])->sum('amount'),
                'offline_donations' => (int) (($offline[PaymentPurpose::Donation->value] ?? 0) + ($offline[PaymentPurpose::Ticket->value] ?? 0)),
                'offline_memberships' => (int) ($offline[PaymentPurpose::Membership->value] ?? 0),
            ];
        });
    }

    /** Online payment status breakdown for the last 30 days. */
    public function onlinePayments(): array
    {
        return Cache::remember('admin.dashboard.online', self::TTL, fn () => Payment::where('mode', PaymentMode::Online)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('status, COUNT(*) c, SUM(amount) total')->groupBy('status')->get()
            ->map(fn ($r) => ['status' => $r->status, 'count' => (int) $r->c, 'total' => (float) $r->total])
            ->sortByDesc('count')->values()->all());
    }

    /** Open campaigns with their live totals (permanent Samaj Donation first). */
    public function campaigns()
    {
        return DonationCampaign::open()->get(['id', 'slug', 'title_en', 'raised_amount', 'target_amount', 'donor_count', 'is_permanent']);
    }
}
