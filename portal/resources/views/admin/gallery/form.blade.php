@php $editing = $album->exists; $max = \App\Models\GalleryAlbum::MAX_UPLOAD; @endphp
<x-layouts.admin :title="$editing ? 'Edit Album' : 'Add Album'">
    <div class="admin-page-head">
        <div>
            <h2>{{ $editing ? $album->title_en : 'Add Gallery Album' }}</h2>
            @if ($editing)<p class="muted">{{ $album->images_count }} photo(s) · {{ $album->is_active ? 'Active' : 'Inactive' }}@if ($album->is_active && $album->images_count) · <a href="{{ route('gallery.show', $album) }}" target="_blank" rel="noopener">View on website</a>@endif</p>@endif
        </div>
        <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline">&larr; All Albums</a>
    </div>

    <form method="POST" novalidate class="admin-panel settings-panel content-section"
          action="{{ $editing ? route('admin.gallery.update', $album->id) : route('admin.gallery.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-head"><h3>Album details</h3></div>
        <div class="admin-panel-body form-grid">
            <x-admin.translatable-field name="title" label="Album title" :model="$album" required hint="Shown above the photos on the Gallery page, e.g. “Diwali Milan 2026”." />
            <x-admin.translatable-field name="description" label="Description" type="textarea" :rows="3" :model="$album" hint="Optional, one or two lines." />
            <div class="form-row">
                <x-field name="event_date" type="date" label="Event date" :value="$album->event_date?->toDateString()" hint="Optional. Shown with the album." />
                <x-field name="sort_order" type="number" label="Display order" :value="$album->sort_order" min="0" max="9999" hint="Lower numbers appear first." />
            </div>
            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $album->is_active))> Visible on the website (when it has photos)</label>
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> {{ $editing ? 'Save Details' : 'Create Album & Add Photos' }}</button></div>
        </div>
    </form>

    @if ($editing)
        <div class="admin-panel content-section">
            <div class="admin-panel-head"><h3>Upload photos</h3></div>
            <div class="admin-panel-body">
                <form method="POST" action="{{ route('admin.gallery.upload', $album->id) }}" enctype="multipart/form-data" class="form-grid" novalidate>
                    @csrf
                    <x-field name="photos[]" id="g-photos" type="file" label="Choose photos" required multiple accept="image/jpeg,image/png,image/webp"
                             hint="Up to {{ $max }} photos at a time · JPG, PNG or WebP · max {{ (int) (config('portal.uploads.image_max_kb') / 1024) }} MB each. Photos are resized automatically for fast loading." />
                    @error('photos')<p class="form-alert form-alert-danger" role="alert">{{ $message }}</p>@enderror
                    @foreach ($errors->get('photos.*') as $messages)<p class="form-alert form-alert-danger" role="alert">{{ $messages[0] }}</p>@break @endforeach
                    <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Upload Photos</button></div>
                </form>
            </div>
        </div>

        <div class="admin-panel content-section">
            <div class="admin-panel-head"><h3>Photos in this album</h3></div>
            <div class="admin-panel-body">
                @if ($images->isEmpty())
                    <x-empty-state title="No photos yet" icon="image"><p>Upload photos above. The album appears on the website once it has at least one photo.</p></x-empty-state>
                @else
                    <p class="muted">The cover photo is shown on the homepage and album list.</p>
                    <ul class="gallery-admin-grid">
                        @foreach ($images as $img)
                            @php $isCover = $album->cover_image_id === $img->id; @endphp
                            <li @class(['gallery-admin-item', 'is-cover' => $isCover])>
                                <a href="{{ $img->fullUrl() }}" target="_blank" rel="noopener" class="gallery-admin-thumb">
                                    <x-img :image="$img->image" alt="Photo {{ $loop->iteration }}" sizes="200px" />
                                </a>
                                <form method="POST" action="{{ route('admin.gallery.images.destroy', $img->id) }}" class="gallery-delete" data-confirm="Delete this photo permanently?">@csrf @method('DELETE')
                                    <button type="submit" aria-label="Delete photo {{ $loop->iteration }}" title="Delete photo">&times;</button></form>
                                @if ($isCover)
                                    <span class="gallery-cover-label">&#9733; Cover photo</span>
                                @else
                                    <form method="POST" action="{{ route('admin.gallery.images.cover', $img->id) }}">@csrf @method('PATCH')
                                        <button type="submit" class="gallery-cover-btn">Set as cover</button></form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="admin-panel content-section">
            <div class="admin-panel-body row-actions">
                <form method="POST" action="{{ route('admin.gallery.destroy', $album->id) }}" data-confirm="Delete “{{ $album->title_en }}” and all {{ $album->images_count }} photo(s) permanently?">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline">Delete Album</button></form>
            </div>
        </div>
    @endif
</x-layouts.admin>
