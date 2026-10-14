@php $editing = $item->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit Announcement' : 'Add Announcement'">
    <div class="admin-page-head">
        <div><h2>{{ $editing ? 'Edit Announcement' : 'Add Announcement' }}</h2></div>
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline">&larr; All Announcements</a>
    </div>

    <form method="POST" novalidate class="admin-panel settings-panel"
          action="{{ $editing ? route('admin.announcements.update', $item) : route('admin.announcements.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            <div class="form-row">
                <x-field name="type" type="select" label="Type" :options="\App\Models\Announcement::TYPES" :value="$item->type" required />
                <x-field name="event_date" type="date" label="Event date (optional)" :value="$item->event_date?->toDateString()" />
            </div>
            <x-admin.translatable-field name="title" label="Title" :model="$item" required />
            <x-admin.translatable-field name="body" label="Details" type="textarea" :rows="3" :model="$item" />
            <div class="form-row">
                <x-field name="starts_at" type="date" label="Show from (optional)" :value="$item->starts_at?->toDateString()" />
                <x-field name="ends_at" type="date" label="Hide after (optional)" :value="$item->ends_at?->toDateString()" />
            </div>
            <div class="form-row">
                <x-field name="sort_order" type="number" label="Display order" :value="$item->sort_order" min="0" max="9999" />
                <label class="checkbox-row align-self-end"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))> Active</label>
            </div>
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Save</button></div>
        </div>
    </form>
</x-layouts.admin>
