@php use App\Enums\MembershipStatus; @endphp
<x-layouts.admin title="Memberships">
    <div class="admin-page-head">
        <div>
            <h2>Lifetime Membership Management</h2>
            <p class="muted">Membership is a one-time payment with lifetime validity — there is no renewal, no expiry date and no recurring fee. A member becomes a Lifetime Member automatically once the fee is confirmed <em>and</em> the profile is verified.</p>
        </div>
    </div>

    <div class="admin-stats-grid">
        @foreach ([MembershipStatus::Active, MembershipStatus::PaymentPending, MembershipStatus::PaymentSubmitted, MembershipStatus::UnderVerification] as $s)
            <a href="{{ route('admin.memberships.index', ['status' => $s->value]) }}" class="admin-stat-card stat-link">
                <div class="label">{{ $s === MembershipStatus::Active ? 'Lifetime Members' : $s->label() }}</div>
                <div class="value">{{ number_format($counts[$s->value] ?? 0) }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid grid-2 align-start content-section">
        <form method="POST" action="{{ route('admin.memberships.fee') }}" class="admin-panel" novalidate>
            @csrf @method('PUT')
            <div class="admin-panel-head"><h3>Membership Fee Settings</h3></div>
            <div class="admin-panel-body form-grid">
                <x-field name="membership_fee" type="number" label="One-time Lifetime Membership Fee (₹)" :value="$fee" min="0" step="1" required
                         hint="Applies to members who have not paid yet. Lifetime members are never charged again." />
                @can('memberships.manage')<div><button type="submit" class="btn btn-primary">Save Fee</button></div>@endcan
            </div>
        </form>

        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Membership Payments Awaiting Verification</h3><span class="pill pill-warning">{{ $pendingPayments->count() }}</span></div>
            @if ($pendingPayments->isEmpty())
                <div class="admin-panel-body"><p class="muted">No offline membership payments are waiting.</p></div>
            @else
                <div class="data-table-wrap borderless">
                    <table class="data-table">
                        <thead><tr><th scope="col">Member</th><th scope="col">Amount</th><th scope="col">Method / Ref</th><th scope="col">Submitted</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                        <tbody>
                            @foreach ($pendingPayments as $p)
                                <tr>
                                    <td data-label="Member">{{ $p->displayName() }}<br><span class="muted">{{ $p->payer_mobile }}</span></td>
                                    <td data-label="Amount">{{ inr($p->amount) }}</td>
                                    <td data-label="Method / Ref">{{ $p->offline_method?->label() }}<br><span class="muted ref-text">{{ $p->offline_reference ?? '—' }}</span></td>
                                    <td data-label="Submitted">{{ $p->created_at->format('d M Y') }}</td>
                                    <td data-label="Actions"><a href="{{ route('admin.payments.show', $p) }}" class="btn btn-primary btn-sm">Review</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="admin-panel">
        <div class="admin-panel-head">
            <h3>All Members — Membership Status</h3>
            <div class="row-actions">
                <a href="{{ route('admin.memberships.index') }}" @class(['btn', 'btn-sm', 'btn-primary' => ! $status, 'btn-outline' => (bool) $status])>All</a>
                @foreach (MembershipStatus::cases() as $s)
                    @continue($s === MembershipStatus::Pending)
                    <a href="{{ route('admin.memberships.index', ['status' => $s->value]) }}" @class(['btn', 'btn-sm', 'btn-primary' => $status === $s, 'btn-outline' => $status !== $s])>{{ $s->label() }}</a>
                @endforeach
            </div>
        </div>
        @if ($memberships->isEmpty())
            <x-empty-state title="No memberships found" icon="search"><p>Membership records are created when members open their membership page or pay.</p></x-empty-state>
        @else
            <div class="data-table-wrap borderless">
                <table class="data-table">
                    <thead><tr><th scope="col">Membership ID</th><th scope="col">Member</th><th scope="col">Membership</th><th scope="col">Profile</th><th scope="col">Fee</th><th scope="col">Lifetime since</th></tr></thead>
                    <tbody>
                        @foreach ($memberships as $ms)
                            <tr>
                                <td data-label="Membership ID">{{ $ms->member?->membership_no ?? '—' }}</td>
                                <td data-label="Member">@if ($ms->member)<a href="{{ route('admin.members.show', $ms->member) }}">{{ $ms->member->name }}</a><br><span class="muted">{{ $ms->member->mobile }}</span>@endif</td>
                                <td data-label="Membership"><x-pill :status="$ms->status" /></td>
                                <td data-label="Profile">@if ($ms->member)<x-pill :status="$ms->member->status" />@endif</td>
                                <td data-label="Fee">{{ inr($ms->fee_amount) }}</td>
                                <td data-label="Lifetime since">{{ $ms->activated_at?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap admin-panel-body">{{ $memberships->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
