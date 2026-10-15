{{-- Matrimonial profile detail (design: matrimonial-browse.html profile modal). Approved profiles, or the owner's own. --}}
@php
    $rows = [
        'age' => __('web.matrimonial.age_years', ['age' => $profile->age()]),
        'gender' => $profile->gender->label(),
        'height' => $profile->heightLabel(),
        'marital_status' => $profile->marital_status->label(),
        'gotra' => $profile->gotra,
        'native_place' => $profile->native_place,
        'father' => $profile->father_name,
        'mother' => $profile->mother_name,
        'education' => $profile->education,
        'occupation' => $profile->occupation,
    ];
@endphp
<x-layouts.member :title="$profile->name">
    <section class="section section-tight">
        <div class="container container-form">
            <div class="page-title-row">
                <a href="{{ $own ? localized_route('member.matrimonial.index') : localized_route('member.matrimonial.browse') }}" class="btn btn-outline btn-sm">&larr; {{ $own ? __('web.matrimonial.back_mine') : __('web.matrimonial.browse_title') }}</a>
                @if ($own)<x-pill :status="$profile->status" />@endif
            </div>

            <article class="card mat-detail">
                <div class="card-body">
                    @if ($profile->photos->isNotEmpty())
                        <div class="mat-gallery">
                            @foreach ($profile->photos as $i => $photo)
                                <a href="{{ route('matrimonial.photo', [$photo->id, 800]) }}" target="_blank" rel="noopener" class="mat-gallery-item @if ($i === 0) is-main @endif">
                                    <img src="{{ route('matrimonial.photo', [$photo->id, $i === 0 ? 800 : 320]) }}" alt="{{ $profile->name }}" width="400" height="500" @if ($i > 0) loading="lazy" @endif decoding="async">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <span class="profile-modal-photo thumb-fallback" aria-hidden="true">{{ initials($profile->name) }}</span>
                    @endif

                    <h1 class="mat-detail-name">{{ $profile->name }}</h1>
                    <p class="muted text-center mat-card-meta">{{ $profile->area ? $profile->area->t('name').', ' : '' }}{{ __('web.common.jaipur') }}</p>

                    <dl class="mat-bio">
                        @foreach ($rows as $key => $value)
                            <div class="bio-row"><dt>{{ __('web.matrimonial.'.$key) }}</dt><dd>{{ $value ?: '—' }}</dd></div>
                        @endforeach
                    </dl>

                    @if ($profile->about)
                        <h2 class="mat-subhead">{{ __('web.matrimonial.about') }}</h2>
                        <p class="mat-about">{{ $profile->about }}</p>
                    @endif

                    @if ($profile->bio_data)
                        <a href="{{ route('matrimonial.biodata', $profile) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm btn-block">{{ __('web.matrimonial.view_bio') }}</a>
                    @endif

                    <div class="mat-contact">
                        <h2 class="mat-subhead">{{ __('web.matrimonial.contact') }}</h2>
                        @if ($profile->show_contact && $profile->member)
                            <p>{{ __('web.matrimonial.registered_by') }}: <strong>{{ $profile->member->name }}</strong></p>
                            <a href="tel:+91{{ $profile->member->mobile }}" class="btn btn-primary btn-sm">{{ __('web.matrimonial.call') }} +91 {{ $profile->member->mobile }}</a>
                        @else
                            <p class="muted">{{ __('web.matrimonial.contact_hidden') }}</p>
                        @endif
                    </div>
                </div>
            </article>

            @unless ($own)
                <details class="card mat-report" @if ($errors->has('reason')) open @endif>
                    <summary class="card-body">{{ __('web.matrimonial.report') }}</summary>
                    <div class="card-body">
                        @if ($reported)
                            <p class="muted">{{ __('web.matrimonial.reported') }}</p>
                        @else
                            <form method="POST" action="{{ localized_route('member.matrimonial.report', ['profile' => $profile->ulid]) }}" class="form-grid" data-validate novalidate>
                                @csrf
                                <x-field name="reason" type="select" :label="__('web.matrimonial.report_reason')" :placeholder="__('web.register.select')" :options="__('web.matrimonial.report_reasons')" required data-rule="required" />
                                <x-field name="details" type="textarea" :rows="3" :label="__('web.matrimonial.report_details')" maxlength="1000" />
                                <button type="submit" class="btn btn-danger-outline btn-sm"><span class="spinner" aria-hidden="true"></span> {{ __('web.matrimonial.report_submit') }}</button>
                            </form>
                        @endif
                    </div>
                </details>
            @endunless
            <p class="muted privacy-note">{{ __('web.matrimonial.private_note') }}</p>
        </div>
    </section>
</x-layouts.member>
