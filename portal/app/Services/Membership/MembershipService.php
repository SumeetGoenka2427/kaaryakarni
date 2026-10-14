<?php

namespace App\Services\Membership;

use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Services\Audit\AuditLogger;
use App\Services\Content\PublicContent;
use App\Services\Sms\SmsService;
use App\Services\Support\NumberSequence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Lifetime membership rules. A member becomes a Lifetime Member when BOTH
 * the one-time fee is confirmed AND the profile is verified by admin —
 * whichever happens last triggers activation. Activation is permanent:
 * there is no expiry or renewal.
 */
class MembershipService
{
    public function __construct(
        private readonly NumberSequence $sequence,
        private readonly SmsService $sms,
        private readonly AuditLogger $audit,
    ) {}

    /** The member's membership record, created on first use with the current fee. */
    public function forMember(Member $member): Membership
    {
        $membership = Membership::firstOrCreate(['member_id' => $member->id], ['fee_amount' => (int) setting('membership_fee')]);

        if ($membership->wasRecentlyCreated) {
            $membership->status = MembershipStatus::PaymentPending;
            $membership->save();
        }

        // The fee in force applies until the member actually pays.
        if ($membership->canPay() && (float) $membership->fee_amount !== (float) setting('membership_fee')) {
            $membership->fee_amount = (int) setting('membership_fee');
            $membership->save();
        }

        return $membership;
    }

    /** Called by Membership::paymentSucceeded inside the payment transaction. */
    public function paymentConfirmed(Membership $membership, Payment $payment): void
    {
        $membership->payment_id = $payment->id;
        $membership->status = MembershipStatus::UnderVerification;
        $membership->save();

        $this->activateIfReady($membership->member);
    }

    /** Activate when payment is confirmed and the profile is verified. */
    public function activateIfReady(Member $member): bool
    {
        $membership = Membership::where('member_id', $member->id)->first();

        if (! $membership || $membership->status !== MembershipStatus::UnderVerification || $member->status !== MemberStatus::Verified) {
            return false;
        }

        DB::transaction(function () use ($membership, $member) {
            $membership->status = MembershipStatus::Active;
            $membership->activated_at = now();
            $membership->save();

            if (! $member->membership_no) {
                $year = now()->year;
                $member->membership_no = sprintf('KSJ-%d-%04d', $year, $this->sequence->next("membership-{$year}"));
                $member->save();
            }
        });

        $this->audit->record('membership.activated', $member, "{$member->name} is now a Lifetime Member ({$member->membership_no})");
        $this->sms->queue($member->mobile, 'membership_active', ['name' => $member->firstName(), 'membership_no' => $member->membership_no], $member->locale);
        Cache::forget('public_content.stats');
        PublicContent::flush();

        return true;
    }
}
