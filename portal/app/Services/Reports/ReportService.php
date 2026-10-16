<?php

namespace App\Services\Reports;

use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Models\Area;
use App\Models\BusinessProfile;
use App\Models\Donation;
use App\Models\MatrimonialProfile;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Admin → Reports (admin/reports.html). Each report is a query plus a row
 * mapper, shared by the on-screen preview and the CSV / Excel / PDF exports.
 * The optional date range limits records by their created date.
 */
class ReportService
{
    /** key => [title, description] in the order shown on the page. */
    public const REPORTS = [
        'member' => ['Member Report', 'All members with area, status and verification.'],
        'membership' => ['Membership Report', 'Count of members by membership status, with fees collected.'],
        'business' => ['Business Report', 'All business directory submissions and their status.'],
        'matrimonial' => ['Matrimonial Approval Report', 'Matrimonial profile submissions and approval status.'],
        'donation' => ['Donation Report', 'Donations with campaign, amount, mode and status.'],
        'payment' => ['Payment Report', 'Membership and donation payments with mode and status.'],
        'area' => ['Area-wise Member Report', 'Member counts grouped by Jaipur area / locality.'],
    ];

    public function title(string $key): string
    {
        return self::REPORTS[$key][0] ?? throw new InvalidArgumentException("Unknown report [{$key}]");
    }

    /** Rows as associative arrays (column => value), streamed lazily. */
    public function rows(string $key, ?Carbon $from, ?Carbon $to): iterable
    {
        $range = fn (Builder $q, string $column = 'created_at') => $q
            ->when($from, fn ($q) => $q->where($column, '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($q) => $q->where($column, '<=', $to->copy()->endOfDay()));

        return match ($key) {
            'member' => $range(Member::query())->with('area:id,name_en')->orderBy('id')->lazy(500)->map(fn (Member $m) => [
                'Membership ID' => $m->membership_no ?? '—',
                'Name' => $m->name,
                'Mobile' => $m->mobile,
                'Area' => $m->area?->name_en ?? '—',
                'Status' => $m->status->label(),
                'Registered On' => $m->created_at->format('d-m-Y'),
                'Verified On' => $m->verified_at?->format('d-m-Y') ?? '—',
            ]),

            'membership' => (function () use ($range) {
                $counts = $range(Membership::query())->selectRaw('status, COUNT(*) AS total, SUM(fee_amount) AS fees')
                    ->groupBy('status')->get()->keyBy(fn ($r) => $r->status instanceof MembershipStatus ? $r->status->value : $r->status);

                return collect(MembershipStatus::cases())->map(fn (MembershipStatus $s) => [
                    'Membership Status' => $s->label(),
                    'Members' => (int) ($counts[$s->value]->total ?? 0),
                    'Fee Amount (₹)' => $s === MembershipStatus::Active ? number_format((float) ($counts[$s->value]->fees ?? 0), 2, '.', '') : '—',
                ]);
            })(),

            'business' => $range(BusinessProfile::query(), 'created_at')->with(['category:id,name_en', 'area:id,name_en', 'member:id,name'])->orderBy('id')->lazy(500)->map(fn (BusinessProfile $b) => [
                'Business' => $b->name,
                'Category' => $b->category?->name_en ?? '—',
                'Area' => $b->area?->name_en ?? '—',
                'Owner' => $b->member?->name ?? '—',
                'Status' => $b->status->label(),
                'Featured' => $b->is_featured ? 'Yes' : 'No',
                'Submitted On' => $b->submitted_at?->format('d-m-Y') ?? '—',
                'Approved On' => $b->approved_at?->format('d-m-Y') ?? '—',
            ]),

            'matrimonial' => $range(MatrimonialProfile::query())->with(['area:id,name_en', 'member:id,name'])->orderBy('id')->lazy(500)->map(fn (MatrimonialProfile $p) => [
                // Approval report only: no contact details, photos or bio-data.
                'Name' => $p->name,
                'Gender' => $p->gender->label(),
                'Age' => $p->age(),
                'Area' => $p->area?->name_en ?? '—',
                'Registered By' => $p->member?->name ?? '—',
                'Status' => $p->status->label(),
                'Submitted On' => $p->submitted_at?->format('d-m-Y') ?? '—',
                'Approved On' => $p->approved_at?->format('d-m-Y') ?? '—',
            ]),

            'donation' => $range(Donation::query())->with(['campaign:id,title_en', 'payment:id,mode,status', 'payment.receipt:id,payment_id,receipt_no'])->orderBy('id')->lazy(500)->map(fn (Donation $d) => [
                'Date' => $d->created_at->format('d-m-Y'),
                'Donor' => $d->donor_name.($d->is_anonymous ? ' (anonymous publicly)' : ''),
                'Mobile' => $d->donor_mobile,
                'Campaign' => $d->campaign?->title_en ?? '—',
                'Type' => ucfirst($d->type),
                'Amount (₹)' => number_format((float) $d->amount, 2, '.', ''),
                'Mode' => $d->payment?->mode?->label() ?? '—',
                'Status' => $d->status->label(),
                'Receipt No' => $d->payment?->receipt?->receipt_no ?? '—',
            ]),

            'payment' => $range(Payment::query())->with(['member:id,name', 'receipt:id,payment_id,receipt_no'])->orderBy('id')->lazy(500)->map(fn (Payment $p) => [
                'Date' => $p->created_at->format('d-m-Y'),
                'Payment ID' => $p->ulid,
                'Payer' => $p->displayName(),
                'Purpose' => $p->purpose->label(),
                'Amount (₹)' => number_format((float) $p->amount, 2, '.', ''),
                'Mode' => $p->mode->label(),
                'Provider' => ucfirst($p->provider),
                'Reference' => $p->reference() ?? '—',
                'Status' => $p->status->label(),
                'Receipt No' => $p->receipt?->receipt_no ?? '—',
            ]),

            'area' => (function () use ($from, $to) {
                $members = fn () => Member::query()
                    ->when($from, fn ($q) => $q->where('members.created_at', '>=', $from->copy()->startOfDay()))
                    ->when($to, fn ($q) => $q->where('members.created_at', '<=', $to->copy()->endOfDay()));
                $total = $members()->selectRaw('area_id, COUNT(*) c')->groupBy('area_id')->pluck('c', 'area_id');
                $verified = $members()->where('status', MemberStatus::Verified)->selectRaw('area_id, COUNT(*) c')->groupBy('area_id')->pluck('c', 'area_id');
                $lifetime = $members()->join('memberships', 'memberships.member_id', '=', 'members.id')
                    ->where('memberships.status', MembershipStatus::Active->value)
                    ->selectRaw('members.area_id, COUNT(*) c')->groupBy('members.area_id')->pluck('c', 'area_id');

                return Area::orderBy('sort_order')->get(['id', 'name_en', 'is_active'])->map(fn (Area $a) => [
                    'Area' => $a->name_en.($a->is_active ? '' : ' (inactive)'),
                    'Total Members' => (int) ($total[$a->id] ?? 0),
                    'Verified' => (int) ($verified[$a->id] ?? 0),
                    'Lifetime Members' => (int) ($lifetime[$a->id] ?? 0),
                ]);
            })(),

            default => throw new InvalidArgumentException("Unknown report [{$key}]"),
        };
    }

    /** First rows for the on-screen preview + the total row count. */
    public function preview(string $key, ?Carbon $from, ?Carbon $to, int $limit = 25): array
    {
        $rows = collect();
        $count = 0;
        foreach ($this->rows($key, $from, $to) as $row) {
            if ($count < $limit) {
                $rows->push($row);
            }
            $count++;
        }

        return ['rows' => $rows, 'total' => $count, 'columns' => array_keys($rows->first() ?? [])];
    }
}
