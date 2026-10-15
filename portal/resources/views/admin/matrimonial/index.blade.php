@php use App\Enums\MatrimonialStatus as MS; @endphp
<x-layouts.admin title="Matrimonial">
    <div class="admin-page-head">
        <div><h2>Matrimonial Profile Management</h2><p class="muted">Review, approve and manage private matrimonial profiles. Only approved profiles become visible to eligible, logged-in Lifetime Members.</p></div>
    </div>

    <div class="form-alert form-alert-warning" role="note">
        <span><strong>Private data:</strong> Matrimonial profiles contain sensitive personal information. They are never publicly browsable and are shown only to eligible logged-in members. Handle every detail on this page with care.</span>
    </div>

    <nav class="tabs" aria-label="Profile status">
        @foreach ([MS::Pending, MS::Approved, MS::Rejected, MS::Hidden, MS::Suspended] as $s)
            <a href="{{ route('admin.matrimonial.index', array_filter(['status' => $s->value, 'q' => $q])) }}" @class(['tab-btn', 'is-active' => $current === $s->value])>{{ $s->label() }} <span class="count">{{ $counts[$s->value] ?? 0 }}</span></a>
        @endforeach
        <a href="{{ route('admin.matrimonial.index', ['status' => 'reports']) }}" @class(['tab-btn', 'is-active' => $current === 'reports'])>Reports <span class="count">{{ $openReports }}</span></a>
    </nav>

    @if ($current === 'reports')
        @if ($reports->isEmpty())
            <x-empty-state title="No reports" icon="inbox"><p>Reports raised by members about a matrimonial profile will appear here.</p></x-empty-state>
        @else
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th scope="col">Profile</th><th scope="col">Reason</th><th scope="col">Details</th><th scope="col">Reported by</th><th scope="col">Date</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($reports as $r)
                            <tr>
                                <td data-label="Profile">@if ($r->profile && ! $r->profile->trashed())<a href="{{ route('admin.matrimonial.show', $r->profile) }}">{{ $r->profile->name }}</a> <x-pill :status="$r->profile->status" />@else {{ $r->profile?->name ?? '—' }} (deleted)@endif</td>
                                <td data-label="Reason">{{ __('web.matrimonial.report_reasons.'.$r->reason, [], 'en') }}</td>
                                <td data-label="Details">{{ $r->details ?: '—' }}</td>
                                <td data-label="Reported by">@if ($r->reporter)<a href="{{ route('admin.members.show', $r->reporter) }}">{{ $r->reporter->name }}</a>@endif</td>
                                <td data-label="Date">{{ $r->created_at->format('d M Y') }}</td>
                                <td data-label="Status"><x-pill :variant="$r->status === 'open' ? 'warning' : 'success'">{{ ucfirst($r->status) }}</x-pill></td>
                                <td data-label="Actions">
                                    @if ($r->status === 'open')
                                        @can('matrimonial.manage')
                                            <form method="POST" action="{{ route('admin.matrimonial.reports.resolve', $r) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-outline btn-sm">Mark Resolved</button></form>
                                        @endcan
                                    @else
                                        <span class="muted">{{ $r->resolved_at?->format('d M Y') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $reports->links() }}</div>
        @endif
    @else
        <form method="GET" class="filters-bar">
            <input type="hidden" name="status" value="{{ $current }}">
            <div class="field search-field"><label for="m-q">Search</label><input type="search" id="m-q" name="q" value="{{ $q }}" placeholder="Search by name or area" maxlength="80"></div>
            <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Search</button></div>
        </form>

        @if ($profiles->isEmpty())
            <x-empty-state title="No {{ strtolower(MS::from($current)->label()) }} profiles" icon="search">
                <p>{{ $current === 'pending' ? 'New matrimonial submissions will appear here for review.' : 'Nothing in “'.MS::from($current)->label().'”'.($q ? ' matching “'.$q.'”' : '').'.' }}</p>
            </x-empty-state>
        @else
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead><tr><th scope="col">Name</th><th scope="col">Age / Gender</th><th scope="col">Area</th><th scope="col">Registered by</th>@if (in_array($current, ['rejected', 'suspended'], true))<th scope="col">Reason</th>@endif<th scope="col">Submitted</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($profiles as $p)
                            <tr>
                                <td data-label="Name"><strong>{{ $p->name }}</strong> <span class="muted">({{ ucfirst($p->relation) }})</span>@if ($p->open_reports_count) <span class="pill pill-danger">{{ $p->open_reports_count }} report(s)</span>@endif @if ($p->is_sample)<span class="sample-badge">Sample</span>@endif</td>
                                <td data-label="Age / Gender">{{ $p->age() }} / {{ $p->gender->label() }}</td>
                                <td data-label="Area">{{ $p->area?->name_en ?? '—' }}</td>
                                <td data-label="Registered by">@if ($p->member)<a href="{{ route('admin.members.show', $p->member) }}">{{ $p->member->name }}</a>@endif</td>
                                @if (in_array($current, ['rejected', 'suspended'], true))<td data-label="Reason">{{ $p->admin_note ?? '—' }}</td>@endif
                                <td data-label="Submitted">{{ $p->submitted_at?->format('d M Y') ?? '—' }}</td>
                                <td data-label="Actions"><a href="{{ route('admin.matrimonial.show', $p) }}" @class(['btn', 'btn-sm', 'btn-primary' => $p->status === MS::Pending, 'btn-outline' => $p->status !== MS::Pending])>{{ $p->status === MS::Pending ? 'Review' : 'View' }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $profiles->links() }}</div>
        @endif
    @endif
</x-layouts.admin>
