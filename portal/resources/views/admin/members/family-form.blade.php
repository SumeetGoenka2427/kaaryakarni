@php $editing = $person->exists; @endphp
<x-layouts.admin :title="($editing ? 'Edit' : 'Add').' Family Member'">
    <div class="admin-page-head">
        <div><h2>{{ $editing ? 'Edit '.$person->name : 'Add Family Member' }}</h2><p class="muted">Family of {{ $member->name }} ({{ $member->mobile }})</p></div>
        <a href="{{ route('admin.members.show', [$member, 'tab' => 'family']) }}" class="btn btn-outline">&larr; Back</a>
    </div>
    <form method="POST" enctype="multipart/form-data" class="admin-panel settings-panel" novalidate
          action="{{ $editing ? route('admin.members.family.update', [$member, $person->ulid]) : route('admin.members.family.store', $member) }}">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="admin-panel-body form-grid">
            <div class="form-row">
                <x-field name="relation" type="select" label="Relation" placeholder="Select" :options="\App\Enums\FamilyRelation::options()" :value="$person->relation?->value" required />
                <x-field name="name" label="Full name" :value="$person->name" required maxlength="120" />
            </div>
            <div class="form-row-3">
                <x-field name="gender" type="select" label="Gender" placeholder="Select" :options="\App\Enums\Gender::options()" :value="$person->gender?->value" />
                <x-field name="dob" type="date" label="Date of birth" :value="$person->dob?->toDateString()" />
                <x-field name="marital_status" type="select" label="Marital status" placeholder="Select" :options="\App\Enums\MaritalStatus::options()" :value="$person->marital_status?->value" />
            </div>
            <div class="form-row">
                <x-field name="education" label="Education" :value="$person->education" maxlength="150" />
                <x-field name="occupation" label="Occupation" :value="$person->occupation" maxlength="150" />
            </div>
            <div class="form-row-3">
                <x-field name="mobile" type="tel" label="Mobile" :value="$person->mobile" maxlength="10" />
                <x-field name="email" type="email" label="Email" :value="$person->email" maxlength="191" />
                <x-field name="blood_group" type="select" label="Blood group" placeholder="Select" :options="array_combine(\App\Models\FamilyMember::BLOOD_GROUPS, \App\Models\FamilyMember::BLOOD_GROUPS)" :value="$person->blood_group" />
            </div>
            <x-field name="photo" type="file" label="Photo" accept="image/jpeg,image/png,image/webp" />
            <x-field name="notes" type="textarea" :rows="2" label="Notes" :value="$person->notes" maxlength="500" />
            <div class="flex flex-wrap gap-4">
                <label class="checkbox-row"><input type="hidden" name="lives_with_member" value="0"><input type="checkbox" name="lives_with_member" value="1" @checked(old('lives_with_member', $person->lives_with_member))> Lives with member</label>
                <label class="checkbox-row"><input type="hidden" name="is_deceased" value="0"><input type="checkbox" name="is_deceased" value="1" @checked(old('is_deceased', $person->is_deceased))> Late (deceased)</label>
            </div>
            <div><button type="submit" class="btn btn-primary">Save</button></div>
        </div>
    </form>
</x-layouts.admin>
