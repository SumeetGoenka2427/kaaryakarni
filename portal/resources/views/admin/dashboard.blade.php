{{-- Admin dashboard (admin/index.html). Every figure is live data; cards link to the filtered list. --}}
@php
    $c = $counts;
    $cards = [
        ['Total Members', number_format($c['members']), 'members.view', route('admin.members.index'), false],
        ['Lifetime Members', number_format($c['lifetime']), 'memberships.view', route('admin.memberships.index', ['status' => 'active']), false],
        ['Pending Member Verification', number_format($c['members_under_verification']), 'members.view', route('admin.members.index', ['status' => 'under_verification']), $c['members_under_verification'] > 0],
        ['Approved Businesses', number_format($c['businesses_approved']), 'businesses.view', route('admin.businesses.index', ['status' => 'approved']), false],
        ['Pending Business Approvals', number_format($c['businesses_pending']), 'businesses.view', route('admin.businesses.index', ['status' => 'pending']), $c['businesses_pending'] > 0],
        ['Pending Matrimonial Approvals', number_format($c['matrimonial_pending']), 'matrimonial.view', route('admin.matrimonial.index', ['status' => 'pending']), $c['matrimonial_pending'] > 0],
        ['Total Donations Collected', inr($c['donations_total']), 'donations.view', route('admin.donations.index'), false],
        ['Offline Donations to Verify', number_format($c['offline_donations']), 'payments.view', route('admin.payments.index', ['mode' => 'offline', 'status' => 'submitted', 'purpose' => 'donation']), $c['offline_donations'] > 0],
    ];
    $pending = [
        ['Members under verification', $c['members_under_verification'], 'members.view', route('admin.members.index', ['status' => 'under_verification'])],
        ['Offline membership payments to verify', $c['offline_memberships'], 'payments.view', route('admin.payments.index', ['mode' => 'offline', 'status' => 'submitted', 'purpose' => 'membership'])],
        ['Business submissions', $c['businesses_pending'], 'businesses.view', route('admin.businesses.index', ['status' => 'pending'])],
        ['Matrimonial profiles', $c['matrimonial_pending'], 'matrimonial.view', route('admin.matrimonial.index', ['status' => 'pending'])],
        ['Offline donations to verify', $c['offline_donations'], 'payments.view', route('admin.payments.index', ['mode' => 'offline', 'status' => 'submitted', 'purpose' => 'donation'])],
    ];
@endphp
<x-layouts.admin title="Dashboard">
    <div class="admin-page-head">
        <div>
            <h2>Samaj Overview</h2>
            <p class="muted">Welcome, {{ auth('admin')->user()->name }}. Live snapshot of members, businesses, matrimonial approvals and donations.</p>
        </div>
    </div>

    <div class="admin-stats-grid">
        @foreach ($cards as [$label, $value, $perm, $url, $alert])
            @can($perm)
                <a href="{{ $url }}" @class(['admin-stat-card', 'is-alert' => $alert])>
                    <div class="label">{{ $label }}</div>
                    <div class="value">{{ $value }}</div>
                </a>
            @else
                <div class="admin-stat-card"><div class="label">{{ $label }}</div><div class="value">{{ $value }}</div></div>
            @endcan
        @endforeach
    </div>

    <div class="grid grid-2 grid-compact align-start">
        @can('members.view')
            <div class="admin-panel">
                <div class="admin-panel-head"><h3>Recent Members</h3><a href="{{ route('admin.members.index') }}" class="btn btn-outline btn-sm">View All</a></div>
                @if ($recentMembers->isEmpty())
                    <div class="admin-panel-body"><p class="muted no-margin">No members have registered yet.</p></div>
                @else
                    <div class="data-table-wrap table-flush">
                        <table class="data-table table-compact">
                            <thead><tr><th scope="col">Member</th><th scope="col">Area</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                @foreach ($recentMembers as $m)
                                    <tr>
                                        <td data-label="Member"><a href="{{ route('admin.members.show', $m) }}">{{ $m->name ?: '—' }}</a><div class="muted">{{ $m->membership_no ?? 'No membership ID yet' }}</div></td>
                                        <td data-label="Area">{{ $m->area?->name_en ?? '—' }}</td>
                                        <td data-label="Status"><x-pill :status="$m->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endcan

        @can('donations.view')
            <div class="admin-panel">
                <div class="admin-panel-head"><h3>Recent Donations</h3><a href="{{ route('admin.donations.index') }}" class="btn btn-outline btn-sm">View All</a></div>
                @if ($recentDonations->isEmpty())
                    <div class="admin-panel-body"><p class="muted no-margin">No donations yet.</p></div>
                @else
                    <div class="data-table-wrap table-flush">
                        <table class="data-table table-compact">
                            <thead><tr><th scope="col">Donor</th><th scope="col">Amount</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                @foreach ($recentDonations as $d)
                                    <tr>
                                        <td data-label="Donor">{{ $d->donor_name }}<div class="muted">{{ $d->campaign?->title_en ?? '—' }}</div></td>
                                        <td data-label="Amount">{{ inr($d->amount) }}</td>
                                        <td data-label="Status"><x-pill :status="$d->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endcan
    </div>

    <div class="grid grid-2 grid-compact align-start content-section-top">
        <div class="admin-panel">
            <div class="admin-panel-head"><h3>Pending Approvals</h3></div>
            <div class="admin-panel-body stack">
                @foreach ($pending as [$label, $count, $perm, $url])
                    @can($perm)
                        <div class="pending-row">
                            <span>{{ $label }}</span>
                            <a href="{{ $url }}" class="pill {{ $count ? 'pill-warning' : 'pill-success' }}">{{ $count ? number_format($count).' pending' : 'All clear' }}</a>
                        </div>
                    @endcan
                @endforeach
            </div>
        </div>

        @can('audit.view')
            <div class="admin-panel">
                <div class="admin-panel-head"><h3>Recent Admin Actions</h3><a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline btn-sm">View All</a></div>
                <div class="admin-panel-body stack">
                    @forelse ($recentActions as $log)
                        <div class="audit-row">
                            <strong>{{ $log->admin?->name ?? 'System' }}</strong>
                            <span class="muted">{{ $log->description ?? $log->action }}</span>
                            <time class="muted" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d M Y, h:i A') }}</time>
                        </div>
                    @empty
                        <p class="muted no-margin">No admin actions recorded yet.</p>
                    @endforelse
                </div>
            </div>
        @endcan
    </div>

    @if ($campaigns->isNotEmpty() || $online)
        <div class="grid grid-2 grid-compact align-start content-section-top">
            @if ($campaigns->isNotEmpty())
                <div class="admin-panel">
                    <div class="admin-panel-head"><h3>Current Campaign Collections</h3><a href="{{ route('admin.campaigns.index') }}" class="btn btn-outline btn-sm">Campaigns</a></div>
                    <div class="admin-panel-body stack">
                        @foreach ($campaigns as $camp)
                            @php $pct = $camp->progressPercent(); @endphp
                            <div>
                                <div class="pending-row">
                                    <strong>{{ $camp->title_en }}</strong>
                                    <span>{{ inr($camp->raised_amount) }}@if ($camp->target_amount) <span class="muted">/ {{ inr($camp->target_amount) }}</span>@endif</span>
                                </div>
                                <div class="muted">{{ number_format($camp->donor_count) }} donor(s){{ $pct !== null ? ' · '.$pct.'%' : '' }}</div>
                                @if ($pct !== null)<progress class="mini-progress" value="{{ $pct }}" max="100" aria-label="{{ $camp->title_en }}: {{ $pct }}% of target">{{ $pct }}%</progress>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @can('payments.view')
                <div class="admin-panel">
                    <div class="admin-panel-head"><h3>Online Payments (last 30 days)</h3><a href="{{ route('admin.payments.index', ['mode' => 'online']) }}" class="btn btn-outline btn-sm">Payments</a></div>
                    <div class="admin-panel-body stack">
                        @forelse ($online as $row)
                            <div class="pending-row">
                                <a href="{{ route('admin.payments.index', ['mode' => 'online', 'status' => $row['status']->value]) }}"><x-pill :status="$row['status']" /></a>
                                <span>{{ number_format($row['count']) }} · {{ inr($row['total']) }}</span>
                            </div>
                        @empty
                            <p class="muted no-margin">No online payments in the last 30 days.</p>
                        @endforelse
                    </div>
                </div>
            @endcan
        </div>
    @endif
</x-layouts.admin>
