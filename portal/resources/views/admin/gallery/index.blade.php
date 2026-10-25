<x-layouts.admin title="Gallery">
    <div class="admin-page-head">
        <div>
            <h2>Gallery Album Management</h2>
            <p class="muted">Create albums, manage their photos and control which albums and titles appear on the public Gallery page and homepage. Albums appear publicly only when active and with at least one photo.</p>
        </div>
        <a href="{{ route('admin.gallery.create') }}" class="btn btn-primary">+ Add Album</a>
    </div>

    <form method="GET" class="filters-bar">
        <div class="field search-field"><label for="g-q">Search</label><input type="search" id="g-q" name="q" value="{{ $q }}" placeholder="Album title" maxlength="80"></div>
        <div class="flex gap-3"><button type="submit" class="btn btn-primary btn-sm">Search</button></div>
    </form>

    @if ($albums->isEmpty())
        <x-empty-state title="No gallery albums are currently available" icon="image">
            <p>{{ $q ? 'No album matches “'.$q.'”.' : 'Add the first album to feature it on the homepage and Gallery page.' }}</p>
            @unless ($q)<a href="{{ route('admin.gallery.create') }}" class="btn btn-primary">+ Add Album</a>@endunless
        </x-empty-state>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th scope="col">Album</th><th scope="col">Event date</th><th scope="col">Photos</th><th scope="col">Order</th><th scope="col">Status</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($albums as $album)
                        <tr>
                            <td data-label="Album">
                                <div class="flex gap-3 align-center">
                                    @if ($album->cover)<x-img :image="$album->cover->image" alt="" class="table-thumb" sizes="64px" />@else<span class="table-thumb initials-avatar" aria-hidden="true">—</span>@endif
                                    <div>
                                        <strong>{{ $album->title_en }}</strong>@if ($album->is_sample) <span class="sample-badge">Sample</span>@endif
                                        @if ($album->title_hi)<div class="muted">{{ $album->title_hi }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Event date">{{ $album->event_date?->format('d M Y') ?? '—' }}</td>
                            <td data-label="Photos">{{ $album->images_count }}</td>
                            <td data-label="Order">{{ $album->sort_order }}</td>
                            <td data-label="Status">
                                @if (! $album->is_active)<x-pill variant="neutral">Inactive</x-pill>
                                @elseif ($album->images_count === 0)<x-pill variant="warning">No photos</x-pill>
                                @else<x-pill variant="success">Active</x-pill>@endif
                            </td>
                            <td data-label="Actions">
                                <x-admin.actions>
                                    <a href="{{ route('admin.gallery.edit', $album->id) }}" class="btn btn-primary btn-sm">Manage Photos</a>
                                    <form method="POST" action="{{ route('admin.gallery.toggle', $album->id) }}" @if ($album->is_active) data-confirm="Hide “{{ $album->title_en }}” from the website?" @endif>@csrf @method('PATCH')
                                        <button type="submit" class="btn btn-outline btn-sm">{{ $album->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                                    @if ($album->is_active && $album->images_count)
                                        <a href="{{ route('gallery.show', $album) }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">View</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.gallery.destroy', $album->id) }}" data-confirm="Delete “{{ $album->title_en }}” and all {{ $album->images_count }} photo(s) permanently?">@csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-sm">Delete</button></form>
                                </x-admin.actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $albums->links() }}</div>
    @endif
</x-layouts.admin>
