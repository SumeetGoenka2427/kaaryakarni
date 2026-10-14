<x-layouts.admin title="Members">
    <div class="admin-page-head">
        <div><h2>Member Management</h2><p class="muted">Search, verify, reject or suspend Samaj member profiles.</p></div>
        <div class="row-actions">
            <a href="{{ route('admin.members.export', request()->query()) }}" class="btn btn-outline">Export CSV</a>
            @can('members.manage')<a href="{{ route('admin.members.create') }}" class="btn btn-primary">+ Register Member</a>@endcan
        </div>
    </div>

    <nav class="tabs" aria-label="Filter by status">
        <a href="{{ route('admin.members.index', collect($filters)->except('status')->all()) }}" @class(['tab-btn', 'is-active' => empty($filters['status'])])>All <span class="count">{{ $statusCounts->sum() }}</span></a>
        @foreach (\App\Enums\MemberStatus::cases() as $s)
            <a href="{{ route('admin.members.index', array_merge($filters, ['status' => $s->value])) }}" @class(['tab-btn', 'is-active' => ($filters['status'] ?? null) === $s->value])>{{ $s->label() }} <span class="count">{{ $statusCounts[$s->value] ?? 0 }}</span></a>
        @endforeach
    </nav>

    <form method="GET" class="filters-bar">
        @if (! empty($filters['status']))<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
        <div class="field search-field"><label for="f-q">Search</label><input type="search" id="f-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, mobile or membership ID"></div>
        <div class="field"><label for="f-area">Area</label>
            <select id="f-area" name="area"><option value="">All Areas</option>
                @foreach ($areas as $id => $name)<option value="{{ $id }}" @selected((int) ($filters['area'] ?? 0) === $id)>{{ $name }}</option>@endforeach
            </select>
        </div>
        <div class="field"><label for="f-from">Registered from</label><input type="date" id="f-from" name="from" value="{{ $filters['from'] ?? '' }}"></div>
        <div class="field"><label for="f-to">to</label><input type="date" id="f-to" name="to" value="{{ $filters['to'] ?? '' }}"></div>
        <div class="flex gap-3">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if (array_filter($filters))<a href="{{ route('admin.members.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
        </div>
    </form>

    @if ($members->isEmpty())
        <x-empty-state title="No members found" icon="search"><p>Try clearing a filter or search term.</p></x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Membership ID</th><th scope="col">Name</th><th scope="col">Mobile</th><th scope="col">Area</th><th scope="col">Status</th><th scope="col">Registered</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($members as $m)
                        <tr>
                            <td data-label="Membership ID">{{ $m->membership_no ?? '—' }}</td>
                            <td data-label="Name"><a href="{{ route('admin.members.show', $m) }}"><strong>{{ $m->name ?? '(profile incomplete)' }}</strong></a></td>
                            <td data-label="Mobile">{{ $m->mobile }}</td>
                            <td data-label="Area">{{ $m->area?->name_en ?? '—' }}</td>
                            <td data-label="Status"><x-pill :status="$m->status" /></td>
                            <td data-label="Registered">{{ $m->created_at->format('d M Y') }}</td>
                            <td data-label="Actions"><a href="{{ route('admin.members.show', $m) }}" class="btn btn-outline btn-sm">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $members->links() }}</div>
    @endif
</x-layouts.admin>
