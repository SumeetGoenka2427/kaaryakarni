<x-layouts.admin title="Edit Area">
    <div class="admin-page-head">
        <div><h2>Edit Area</h2><p class="muted">Renaming an area updates it everywhere, including existing member profiles.</p></div>
        <a href="{{ route('admin.areas.index') }}" class="btn btn-outline">&larr; All Areas</a>
    </div>
    <form method="POST" action="{{ route('admin.areas.update', $area) }}" class="admin-panel settings-panel" novalidate>
        @csrf @method('PUT')
        <div class="admin-panel-body form-grid">
            <div class="form-row">
                <x-field name="name_en" label="Area name (English)" :value="$area->name_en" required maxlength="100" />
                <x-field name="name_hi" label="Area name (हिन्दी)" :value="$area->name_hi" maxlength="100" lang="hi" />
            </div>
            <div><button type="submit" class="btn btn-primary">Save Area</button></div>
        </div>
    </form>
</x-layouts.admin>
