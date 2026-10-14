<?php

namespace App\Services\Members;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\MemberVerification;
use App\Services\Audit\AuditLogger;
use App\Services\Content\PublicContent;
use App\Services\Sms\SmsService;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The only place a member's verification status changes. Each change is
 * validated against the allowed transitions, recorded in the member's
 * verification history and the audit log, and the member gets an SMS.
 */
class MemberService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly SmsService $sms,
    ) {}

    public function recordSubmitted(Member $member): void
    {
        MemberVerification::create(['member_id' => $member->id, 'action' => 'submitted']);
    }

    public function verify(Member $member): void
    {
        $this->guard($member, [MemberStatus::UnderVerification, MemberStatus::Rejected, MemberStatus::Pending], 'verify');

        $this->transition($member, MemberStatus::Verified, 'verified', null, function () use ($member) {
            $member->verified_at = now();
            $member->rejection_reason = null;
        });
        $this->sms->queue($member->mobile, 'member_verified', ['name' => $member->firstName()], $member->locale);
    }

    public function reject(Member $member, string $reason): void
    {
        $this->guard($member, [MemberStatus::UnderVerification, MemberStatus::Pending, MemberStatus::Verified], 'reject');

        $this->transition($member, MemberStatus::Rejected, 'rejected', $reason, function () use ($member, $reason) {
            $member->rejection_reason = $reason;
        });
        $this->sms->queue($member->mobile, 'member_rejected', ['name' => $member->firstName()], $member->locale);
    }

    public function suspend(Member $member, string $reason): void
    {
        if ($member->status === MemberStatus::Suspended) {
            throw new DomainException('This member is already suspended.');
        }

        $this->transition($member, MemberStatus::Suspended, 'suspended', $reason, function () use ($member) {
            $member->suspended_at = now();
        });
        $this->sms->queue($member->mobile, 'member_suspended', ['name' => $member->firstName()], $member->locale);
    }

    public function reinstate(Member $member): void
    {
        $this->guard($member, [MemberStatus::Suspended], 'reinstate');

        // Back to where they were before the suspension.
        $status = $member->verified_at ? MemberStatus::Verified : MemberStatus::UnderVerification;
        $this->transition($member, $status, 'reinstated', null, function () use ($member) {
            $member->suspended_at = null;
        });
        $this->sms->queue($member->mobile, 'member_reinstated', ['name' => $member->firstName()], $member->locale);
    }

    private function guard(Member $member, array $allowedFrom, string $action): void
    {
        if (! in_array($member->status, $allowedFrom, true)) {
            throw new DomainException("Cannot {$action} a member whose status is “{$member->status->label()}”.");
        }
    }

    private function transition(Member $member, MemberStatus $to, string $action, ?string $reason, callable $apply): void
    {
        $from = $member->status;

        DB::transaction(function () use ($member, $to, $action, $reason, $apply) {
            $apply();
            $member->status = $to;
            $member->save();

            MemberVerification::create([
                'member_id' => $member->id,
                'action' => $action,
                'reason' => $reason,
                'admin_user_id' => auth('admin')->id(),
            ]);
        });

        $this->audit->record("member.{$action}", $member, "{$member->name} ({$member->mobile}): {$from->label()} → {$to->label()}".($reason ? " — {$reason}" : ''),
            ['status' => $from->value], ['status' => $to->value]);

        Cache::forget('public_content.stats');
        PublicContent::flush();
    }
}
