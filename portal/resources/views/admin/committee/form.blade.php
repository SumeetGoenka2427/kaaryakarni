@php $editing = $member->exists; @endphp
<x-layouts.admin :title="$editing ? 'Edit Committee Member' : 'Add Committee Member'">
    <div class="admin-page-head">
        <div><h2>{{ $editing ? 'Edit '.$member->name_en : 'Add Committee Member' }}</h2></div>
        <a href="{{ route('admin.committee.index') }}" class="btn btn-outline">&larr; All Members</a>
    </div>

    <form method="POST" enctype="multipart/form-data" novalidate class="admin-panel settings-panel"
          action="{{ $editing ? route('admin.committee.update', $member) : route('admin.committee.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            <x-admin.translatable-field name="name" label="Name" :model="$member" required hint="e.g. Shri Ramesh Khandelwal" />
            <x-admin.translatable-field name="designation" label="Designation" :model="$member" required hint="e.g. President, Treasurer, Executive Committee Member" />
            <div class="form-row">
                <x-field name="tier" type="select" label="Group" :options="\App\Models\CommitteeMember::TIERS" :value="$member->tier" required />
                <x-field name="sort_order" type="number" label="Display order" :value="$member->sort_order" min="0" max="9999" hint="Lower numbers appear first." />
            </div>
            <x-admin.image-field name="photo" label="Photo" :current="$member->photo" hint="Square photo works best. Shown for Leadership members only." />
            <label class="checkbox-row"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->is_active))> Visible on the website</label>
            <div><button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> Save</button></div>
        </div>
    </form>
</x-layouts.admin>
