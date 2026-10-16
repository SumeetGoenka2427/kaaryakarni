<?php

namespace App\Services\Notifications;

use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Models\Broadcast;
use App\Models\Member;
use App\Models\NotificationTemplate;
use App\Services\Audit\AuditLogger;
use App\Services\Sms\SmsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One place for member notifications (CLAUDE.md §16): every system message
 * goes to the member's in-app inbox (EN + HI text, shown in the member's
 * language) and, through SmsService, by SMS. OTP SMS do not pass through here.
 */
class Notifier
{
    /** Template key → inbox category and the member-area page it links to. */
    public const KINDS = [
        'registration_received' => ['membership', 'member.profile'],
        'member_verified' => ['membership', 'member.membership'],
        'member_rejected' => ['membership', 'member.profile'],
        'member_suspended' => ['membership', 'member.dashboard'],
        'member_reinstated' => ['membership', 'member.dashboard'],
        'membership_active' => ['membership', 'member.membership'],
        'payment_received' => ['payment', 'member.payments'],
        'payment_submitted' => ['payment', 'member.payments'],
        'payment_rejected' => ['payment', 'member.payments'],
        'business_approved' => ['business', 'member.business'],
        'business_rejected' => ['business', 'member.business'],
        'business_changes_requested' => ['business', 'member.business'],
        'matrimonial_approved' => ['matrimonial', 'member.matrimonial.index'],
        'matrimonial_rejected' => ['matrimonial', 'member.matrimonial.index'],
    ];

    public function __construct(
        private readonly SmsService $sms,
        private readonly AuditLogger $audit,
    ) {}

    /** Inbox entry + SMS to a member. */
    public function member(Member $member, string $key, array $vars = [], bool $sms = true): void
    {
        $this->inbox($member, $key, $vars);
        if ($sms) {
            $this->sms->queue($member->mobile, $key, $vars, $member->locale ?? 'en');
        }
    }

    /** SMS to any mobile (e.g. a guest donor); inbox entry too when it belongs to a member. */
    public function mobile(string $mobile, string $key, array $vars, string $locale, ?Member $member = null): void
    {
        if ($member) {
            $this->inbox($member, $key, $vars);
        }
        $this->sms->queue($mobile, $key, $vars, $member?->locale ?? $locale);
    }

    public function inbox(Member $member, string $key, array $vars = []): void
    {
        $template = NotificationTemplate::where('key', $key)->first();
        if (! $template) {
            return;
        }
        [$type, $route] = self::KINDS[$key] ?? ['announcement', null];

        $this->insert([$member->id], [
            'kind' => $key,
            'type' => $type,
            'route' => $route,
            'message_en' => $template->render('en', $vars),
            'message_hi' => $template->render('hi', $vars),
        ]);
    }

    /** Send an admin announcement to its audience; returns the number of members reached. */
    public function broadcast(Broadcast $broadcast): int
    {
        $data = ['kind' => 'broadcast', 'type' => $broadcast->type];
        $total = 0;

        $this->audience($broadcast)->select(['id', 'mobile', 'locale'])->chunkById(500, function ($members) use ($broadcast, $data, &$total) {
            $this->insert($members->pluck('id')->all(), $data, $broadcast->id);
            $total += $members->count();

            if ($broadcast->send_sms) {
                foreach ($members as $m) {
                    $this->sms->queue($m->mobile, 'announcement', [
                        'title' => Str::limit($m->locale === 'hi' ? $broadcast->t('title', 'hi') : $broadcast->title_en, 60),
                        'message' => Str::limit($m->locale === 'hi' ? $broadcast->t('message', 'hi') : $broadcast->message_en, 120),
                    ], $m->locale ?? 'en');
                }
            }
        });

        $broadcast->recipients = $total;
        $broadcast->save();
        $this->audit->record('notifications.broadcast', $broadcast, "Sent announcement “{$broadcast->title_en}” to {$total} member(s)".($broadcast->send_sms ? ' with SMS' : ''));

        return $total;
    }

    /** Members an announcement goes to (suspended and rejected accounts never). */
    public function audience(Broadcast $broadcast): Builder
    {
        return Member::query()
            ->whereNotIn('status', [MemberStatus::Suspended, MemberStatus::Rejected])
            ->when($broadcast->audience === 'lifetime', fn ($q) => $q->whereHas('membership', fn ($m) => $m->where('status', MembershipStatus::Active)))
            ->when($broadcast->audience === 'area', fn ($q) => $q->where('area_id', $broadcast->area_id));
    }

    /** @param list<int> $memberIds */
    private function insert(array $memberIds, array $data, ?int $broadcastId = null): void
    {
        $now = now();
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $rows = array_map(fn ($id) => [
            'id' => (string) Str::uuid(),
            'type' => $broadcastId ? 'broadcast' : 'system',
            'notifiable_type' => (new Member)->getMorphClass(),
            'notifiable_id' => $id,
            'data' => $json,
            'broadcast_id' => $broadcastId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $memberIds);

        DB::table('notifications')->insert($rows);
    }
}
