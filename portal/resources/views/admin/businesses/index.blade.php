@php use App\Enums\BusinessStatus as BS; @endphp
<x-layouts.admin title="Business Directory">
    <div class="admin-page-head">
        <div><h2>Business &amp; Professional Directory</h2><p class="muted">Approve, reject, request changes, feature or hide member business submissions. Only approved businesses appear publicly.</p></div>
        @can('businesses.manage')<a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Categories</a>@endcan
    </div>

    <nav class="tabs" aria-label="Listing status">
        @foreach ([BS::Pending, BS::ChangesRequested, BS::Approved, BS::Rejected, BS::Hidden] as $s)
            <a href="{{ route('admin.businesses.index', array_filter(['status' => $s->value, 'q' => $q])) }}" @class(['tab-btn', 'is-active' => $current === $s->value])>{{ $s->label() }} <span class="count">{{ $counts[$s->value] ?? 0 }}</span></a>
        @endforeach
        <a href="{{ route('admin.businesses.index', array_filter(['status' => 'featured', 'q' => $q])) }}" @class(['tab-btn', 'is-active' => $current === 'featured'])>Featured <span class="count">{{ $featuredCount }}</span></a>
    </nav>

    <form method="GET" class="filters-bar">
        <input type="hidden" name="status" value="{{ $current }}">
        <div class="field search-field"><label for="b-q">Search</label><input type="search" id="b-q" name="q" value="{{ $q }}" placeholder="Search by name, category or area"></div>
        <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Search</button></div>
    </form>

    @if ($businesses->isEmpty())
        <x-empty-state title="No listings here" icon="search"><p>Nothing in “{{ $current === 'featured' ? 'Featured' : BS::from($current)->label() }}”{{ $q ? ' matching “'.$q.'”' : '' }}.</p></x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Business</th><th scope="col">Category</th><th scope="col">Area</th><th scope="col">Owner</th><th scope="col">Status</th><th scope="col">Submitted</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($businesses as $b)
                        <tr>
                            <td data-label="Business"><strong>{{ $b->name }}</strong>@if ($b->is_featured) <span class="pill pill-success">Featured</span>@endif @if ($b->is_sample)<span class="sample-badge">Sample</span>@endif</td>
                            <td data-label="Category">{{ $b->category?->name_en }}</td>
                            <td data-label="Area">{{ $b->area?->name_en }}</td>
                            <td data-label="Owner">@if ($b->member)<a href="{{ route('admin.members.show', $b->member) }}">{{ $b->member->name }}</a>@endif</td>
                            <td data-label="Status"><x-pill :status="$b->status" /></td>
                            <td data-label="Submitted">{{ $b->submitted_at?->format('d M Y') ?? '—' }}</td>
                            <td data-label="Actions"><a href="{{ route('admin.businesses.show', $b->id) }}" @class(['btn', 'btn-sm', 'btn-primary' => $b->status === BS::Pending, 'btn-outline' => $b->status !== BS::Pending])>{{ $b->status === BS::Pending ? 'Review' : 'View' }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $businesses->links() }}</div>
    @endif
</x-layouts.admin>
