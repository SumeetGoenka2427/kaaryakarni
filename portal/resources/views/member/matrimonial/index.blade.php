{{-- Member → My Matrimonial Profiles (web/member/matrimonial.html). Private; admin-approved. --}}
@php
    use App\Enums\MaritalStatus;
    use App\Enums\MatrimonialStatus as MS;
    use App\Models\MatrimonialProfile;
    $max = MatrimonialProfile::MAX_PER_MEMBER;
    $atLimit = $profiles->count() >= $max;
    $p = $editing;
    $v = fn ($key) => $p ? $p->{$key} : ($prefill[$key] ?? null);
@endphp
<x-layouts.member :title="__('web.matrimonial.title')">
    <section class="section section-tight">
        <div class="container">
            <div class="page-title-row">
                <div>
                    <h1>{{ __('web.matrimonial.title') }}</h1>
                    <p class="muted">{{ __('web.matrimonial.intro', ['max' => $max]) }}</p>
                </div>
                @if ($eligible)
                    <div class="flex flex-wrap gap-3">
                        @unless ($atLimit)
                            <a href="{{ localized_route('member.matrimonial.index', ['new' => 1]) }}" class="btn btn-primary btn-sm">+ {{ __('web.matrimonial.add') }}</a>
                        @endunless
                        <a href="{{ localized_route('member.matrimonial.browse') }}" class="btn btn-outline btn-sm">{{ __('web.matrimonial.browse') }} &rarr;</a>
                    </div>
                @endif
            </div>
            <div class="form-alert form-alert-info" role="note">{{ __('web.matrimonial.privacy') }}</div>

            @unless ($eligible)
                <x-empty-state icon="lock" :title="__('web.matrimonial.locked_title')">
                    <p>{{ __('web.matrimonial.locked_text') }}</p>
                    <a href="{{ localized_route('member.membership') }}" class="btn btn-primary">{{ __('web.matrimonial.locked_cta') }}</a>
                </x-empty-state>
            @endunless

            @if ($eligible || $profiles->isNotEmpty())
                <div class="grid grid-2 mat-layout">
                    <div>
                        <h2 class="mat-subhead">{{ __('web.matrimonial.my_profiles') }}</h2>
                        @forelse ($profiles as $profile)
                            <article class="card mat-list-card @if ($p && $p->is($profile)) is-current @endif">
                                <div class="card-body mat-profile-card">
                                    @if ($photo = $profile->photos->first())
                                        <img class="thumb" src="{{ route('matrimonial.photo', [$photo->id, 320]) }}" alt="" width="56" height="56" loading="lazy">
                                    @else
                                        <span class="thumb-fallback" aria-hidden="true">{{ initials($profile->name) }}</span>
                                    @endif
                                    <div class="mat-profile-info">
                                        <div class="flex-between gap-3">
                                            <h3 class="mat-card-name">{{ $profile->name }}</h3>
                                            <x-pill :status="$profile->status" />
                                        </div>
                                        <p class="muted mat-card-meta">
                                            {{ __('web.matrimonial.relations.'.$profile->relation) }} ·
                                            {{ __('web.matrimonial.age_years', ['age' => $profile->age()]) }} / {{ $profile->gender->label() }}
                                            @if ($profile->area) · {{ $profile->area->t('name') }}@endif
                                        </p>
                                        @if ($profile->status === MS::Rejected && $profile->admin_note)
                                            <p class="mat-card-note text-danger">{{ __('web.matrimonial.note_rejected', ['note' => $profile->admin_note]) }}</p>
                                        @elseif ($profile->status === MS::Suspended && $profile->admin_note)
                                            <p class="mat-card-note text-danger">{{ __('web.matrimonial.note_suspended', ['note' => $profile->admin_note]) }}</p>
                                        @endif
                                        <div class="row-actions mat-card-actions">
                                            <a href="{{ localized_route('member.matrimonial.show', ['profile' => $profile->ulid]) }}" class="btn btn-ghost btn-sm">{{ __('web.matrimonial.view') }}</a>
                                            @if ($eligible && $profile->status !== MS::Suspended)
                                                <a href="{{ localized_route('member.matrimonial.index', ['profile' => $profile->ulid]) }}#mat-form" class="btn btn-outline btn-sm">{{ __('web.common.edit') }}</a>
                                            @endif
                                            <form method="POST" action="{{ localized_route('member.matrimonial.destroy', ['profile' => $profile->ulid]) }}" data-confirm="{{ __('web.matrimonial.delete_confirm', ['name' => $profile->name]) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger-outline btn-sm">{{ __('web.common.delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-empty-state :title="__('web.matrimonial.empty')">
                                <p>{{ __('web.matrimonial.empty_text') }}</p>
                            </x-empty-state>
                        @endforelse
                        @if ($atLimit)
                            <p class="muted mat-limit-note">{{ __('web.matrimonial.limit_reached', ['max' => $max]) }}</p>
                        @endif
                    </div>

                    <div>
                        @if ($eligible && $showForm)
                            <div class="card" id="mat-form">
                                <div class="card-body">
                                    <h2 class="mat-subhead">{{ $p ? __('web.matrimonial.edit') : __('web.matrimonial.add') }}</h2>
                                    @if ($p && $p->status === MS::Approved)
                                        <p class="muted register-note">{{ __('web.matrimonial.review_note') }}</p>
                                    @endif
                                    <form method="POST" enctype="multipart/form-data" class="form-grid" data-validate novalidate
                                          action="{{ $p ? localized_route('member.matrimonial.update', ['profile' => $p->ulid]) : localized_route('member.matrimonial.store') }}">
                                        @csrf
                                        @if ($p) @method('PUT') @endif
                                        @if (! $p && ! empty($prefill['family']))<input type="hidden" name="family" value="{{ $prefill['family'] }}">@endif

                                        <div class="form-row">
                                            <x-field name="relation" type="select" :label="__('web.matrimonial.relation')" :options="__('web.matrimonial.relations')" :value="$v('relation') ?? 'self'" required />
                                            <x-field name="name" :label="__('web.matrimonial.name')" :value="$v('name')" required maxlength="120" data-rule="required" />
                                        </div>
                                        <div class="form-row-3">
                                            <x-field name="gender" type="select" :label="__('web.matrimonial.gender')" :placeholder="__('web.register.select')"
                                                     :options="['male' => \App\Enums\Gender::Male->label(), 'female' => \App\Enums\Gender::Female->label()]"
                                                     :value="$p?->gender?->value ?? ($prefill['gender'] ?? null)" required data-rule="required" />
                                            <x-field name="dob" type="date" :label="__('web.matrimonial.dob')" :value="$p?->dob?->toDateString() ?? ($prefill['dob'] ?? null)"
                                                     required data-rule="required" max="{{ now()->subYears(18)->toDateString() }}" />
                                            <x-field name="height_cm" type="select" :label="__('web.matrimonial.height')" :placeholder="__('web.register.select')" :options="MatrimonialProfile::heightOptions()" :value="$v('height_cm')" />
                                        </div>
                                        <div class="form-row-3">
                                            <x-field name="marital_status" type="select" :label="__('web.matrimonial.marital_status')"
                                                     :options="collect(MaritalStatus::cases())->reject(fn ($c) => $c === MaritalStatus::Married)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()"
                                                     :value="$p?->marital_status?->value ?? ($prefill['marital_status'] ?? 'unmarried')" required />
                                            <x-field name="gotra" :label="__('web.matrimonial.gotra')" :value="$v('gotra')" maxlength="80" />
                                            <x-field name="native_place" :label="__('web.matrimonial.native_place')" :value="$v('native_place')" maxlength="120" />
                                        </div>
                                        <div class="form-row">
                                            <x-field name="father_name" :label="__('web.matrimonial.father')" :value="$v('father_name')" maxlength="120" />
                                            <x-field name="mother_name" :label="__('web.matrimonial.mother')" :value="$v('mother_name')" maxlength="120" />
                                        </div>
                                        <div class="form-row-3">
                                            <x-field name="area_id" type="select" :label="__('web.matrimonial.area')" :placeholder="__('web.register.select')" :options="$areas" :value="$p?->area_id ?? $member->area_id" />
                                            <x-field name="education" :label="__('web.matrimonial.education')" :value="$v('education')" maxlength="150" />
                                            <x-field name="occupation" :label="__('web.matrimonial.occupation')" :value="$v('occupation')" maxlength="150" />
                                        </div>
                                        <x-field name="about" type="textarea" :rows="3" :label="__('web.matrimonial.about')" :value="$v('about')" :placeholder="__('web.matrimonial.about_placeholder')" maxlength="1000" />

                                        <div class="mat-photos">
                                            @if ($p && $p->photos->isNotEmpty())
                                                <div class="bio-photo-grid">
                                                    @foreach ($p->photos as $photo)
                                                        <div class="bio-photo">
                                                            <img src="{{ route('matrimonial.photo', [$photo->id, 320]) }}" alt="" width="160" height="200" loading="lazy">
                                                            <label class="checkbox-row"><input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" @checked(in_array($photo->id, old('remove_photos', [])))> {{ __('web.matrimonial.remove_photo') }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <x-field name="photos[]" id="f-photos" type="file" :label="__('web.matrimonial.photos')" :required="! $p"
                                                     :hint="__('web.matrimonial.photos_hint', ['max' => MatrimonialProfile::MAX_PHOTOS])"
                                                     accept="image/jpeg,image/png,image/webp" multiple />
                                            @error('photos')<p class="form-alert form-alert-danger" role="alert">{{ $message }}</p>@enderror
                                            @error('photos.*')<p class="form-alert form-alert-danger" role="alert">{{ $message }}</p>@enderror
                                        </div>

                                        <x-field name="bio_data" type="file" :label="__('web.matrimonial.bio_data')" :hint="__('web.matrimonial.bio_data_hint')" accept="application/pdf" />
                                        @if ($p?->bio_data)
                                            <div class="flex flex-wrap gap-4 mat-bio-current">
                                                <a href="{{ route('matrimonial.biodata', $p) }}" target="_blank" rel="noopener">{{ __('web.matrimonial.view_bio') }}</a>
                                                <label class="checkbox-row"><input type="checkbox" name="remove_bio" value="1"> {{ __('web.matrimonial.remove_bio') }}</label>
                                            </div>
                                        @endif

                                        <label class="checkbox-row"><input type="hidden" name="show_contact" value="0"><input type="checkbox" name="show_contact" value="1" @checked(old('show_contact', $p?->show_contact ?? false))> {{ __('web.matrimonial.show_contact') }}</label>

                                        <div class="flex gap-3">
                                            <button type="submit" class="btn btn-primary flex-1"><span class="spinner" aria-hidden="true"></span> {{ __('web.matrimonial.save') }}</button>
                                            <a href="{{ localized_route('member.matrimonial.index') }}" class="btn btn-outline">{{ __('web.common.cancel') }}</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @elseif ($eligible)
                            <div class="card">
                                <div class="card-body text-center mat-form-placeholder">
                                    <p class="muted">{{ $atLimit ? __('web.matrimonial.limit_reached', ['max' => $max]) : __('web.matrimonial.empty_text') }}</p>
                                    @unless ($atLimit)
                                        <a href="{{ localized_route('member.matrimonial.index', ['new' => 1]) }}#mat-form" class="btn btn-primary btn-sm">+ {{ __('web.matrimonial.add') }}</a>
                                    @endunless
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.member>
