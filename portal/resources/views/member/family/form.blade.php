@php $editing = $person->exists; @endphp
<x-layouts.member :title="__($editing ? 'web.family.edit' : 'web.family.add')">
    <section class="section section-tight">
        <div class="container container-form">
            <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{ localized_route('member.family.index') }}">{{ __('web.family.title') }}</a> / {{ __($editing ? 'web.family.edit' : 'web.family.add') }}</nav>
            <h1>{{ __($editing ? 'web.family.edit' : 'web.family.add') }}</h1>
            <p class="muted">{{ __('web.family.private_note') }}</p>

            <form method="POST" enctype="multipart/form-data" class="form-grid" data-validate novalidate
                  action="{{ $editing ? localized_route('member.family.update', ['family' => $person->ulid]) : localized_route('member.family.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif

                <div class="form-row">
                    <x-field name="relation" type="select" :label="__('web.family.relation')" :placeholder="__('web.register.select')" :options="\App\Enums\FamilyRelation::options()" :value="$person->relation?->value" required data-rule="required" />
                    <x-field name="name" :label="__('web.family.name')" :value="$person->name" required maxlength="120" data-rule="required" />
                </div>
                <div class="form-row-3">
                    <x-field name="gender" type="select" :label="__('web.family.gender')" :placeholder="__('web.register.select')" :options="\App\Enums\Gender::options()" :value="$person->gender?->value" />
                    <x-field name="dob" type="date" :label="__('web.family.dob')" :value="$person->dob?->toDateString()" :max="now()->subDay()->toDateString()" />
                    <x-field name="marital_status" type="select" :label="__('web.family.marital_status')" :placeholder="__('web.register.select')" :options="\App\Enums\MaritalStatus::options()" :value="$person->marital_status?->value" />
                </div>
                <div class="form-row">
                    <x-field name="education" :label="__('web.family.education')" :value="$person->education" maxlength="150" />
                    <x-field name="occupation" :label="__('web.family.occupation')" :value="$person->occupation" maxlength="150" />
                </div>
                <div class="form-row-3">
                    <x-field name="mobile" type="tel" :label="__('web.family.mobile')" :value="$person->mobile" maxlength="10" inputmode="numeric" placeholder="98XXXXXXXX" />
                    <x-field name="email" type="email" :label="__('web.family.email')" :value="$person->email" maxlength="191" />
                    <x-field name="blood_group" type="select" :label="__('web.family.blood_group')" :placeholder="__('web.register.select')" :options="array_combine(\App\Models\FamilyMember::BLOOD_GROUPS, \App\Models\FamilyMember::BLOOD_GROUPS)" :value="$person->blood_group" />
                </div>

                <div class="profile-photo-row">
                    @if ($person->photo)
                        <img src="{{ route('photos.family', $person) }}?v={{ md5($person->photo['path']) }}" alt="" class="family-photo" width="56" height="56">
                    @endif
                    <div class="profile-photo-input">
                        <x-field name="photo" type="file" :label="__('web.family.photo')" accept="image/jpeg,image/png,image/webp" :hint="__('web.profile.photo_hint')" />
                        @if ($person->photo)
                            <label class="checkbox-row"><input type="checkbox" name="remove_photo" value="1"> {{ __('web.profile.remove_photo') }}</label>
                        @endif
                    </div>
                </div>

                <x-field name="notes" type="textarea" :rows="2" :label="__('web.family.notes')" :value="$person->notes" maxlength="500" />

                <div class="flex flex-wrap gap-4">
                    <label class="checkbox-row"><input type="hidden" name="lives_with_member" value="0"><input type="checkbox" name="lives_with_member" value="1" @checked(old('lives_with_member', $person->lives_with_member))> {{ __('web.family.lives_with_member') }}</label>
                    <label class="checkbox-row"><input type="hidden" name="is_deceased" value="0"><input type="checkbox" name="is_deceased" value="1" @checked(old('is_deceased', $person->is_deceased))> {{ __('web.family.is_deceased') }}</label>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn btn-primary"><span class="spinner" aria-hidden="true"></span> {{ __('web.family.save') }}</button>
                    <a href="{{ localized_route('member.family.index') }}" class="btn btn-outline">{{ __('web.common.cancel') }}</a>
                </div>
            </form>
        </div>
    </section>
</x-layouts.member>
