{{-- My Business Profile (web/member/business.html). One listing per member, admin-approved. --}}
@php use App\Enums\BusinessStatus as BS; $st = $business?->status; @endphp
<x-layouts.member :title="__('web.business.my_title')">
    <section class="section section-tight">
        <div class="container container-form">
            <h1>{{ __('web.business.my_title') }}</h1>
            <p class="muted">{{ __('web.business.my_intro') }}</p>

            @if (! $canList && ! $business)
                <div class="form-alert form-alert-warning" role="status">{{ __('web.business.needs_verification') }}</div>
            @else
                @if ($business)
                    <div class="flex-between flex-wrap gap-3 status-row">
                        <span>{{ __('web.business.status') }}: <x-pill :status="$st" /></span>
                        @if ($st === BS::Approved)
                            <a href="{{ localized_route('business.show', ['business' => $business->slug]) }}" class="btn btn-outline btn-sm">{{ __('web.business.view_live') }}</a>
                        @endif
                    </div>
                    @if ($st === BS::Rejected && $business->admin_note)
                        <div class="form-alert form-alert-danger" role="status">{{ __('web.business.note_rejected', ['note' => $business->admin_note]) }}</div>
                    @elseif ($st === BS::ChangesRequested && $business->admin_note)
                        <div class="form-alert form-alert-warning" role="status">{{ __('web.business.note_changes', ['note' => $business->admin_note]) }}</div>
                    @endif
                @endif

                <form method="POST" action="{{ localized_route('member.business.update') }}" enctype="multipart/form-data" class="form-grid" data-validate novalidate>
                    @csrf @method('PUT')
                    <div class="form-row">
                        <x-field name="name" :label="__('web.business.name')" :value="$business?->name" required maxlength="150" data-rule="required" />
                        <x-field name="category_id" type="select" :label="__('web.business.category')" :placeholder="__('web.register.select')" :options="$categories" :value="$business?->category_id" required data-rule="required" />
                    </div>
                    <x-field name="description" type="textarea" :rows="4" :label="__('web.business.description')" :value="$business?->description" required maxlength="2000" :hint="__('web.business.description_hint')" data-rule="required" />
                    <div class="form-row">
                        <x-field name="area_id" type="select" :label="__('web.register.area')" :placeholder="__('web.register.area_placeholder')" :options="$areas" :value="$business?->area_id ?? $member->area_id" required />
                        <x-field name="hours" :label="__('web.business.hours_label')" :value="$business?->hours" :placeholder="__('web.business.hours_placeholder')" maxlength="150" />
                    </div>
                    <x-field name="address" type="textarea" :rows="2" :label="__('web.business.address')" :value="$business?->address" maxlength="500" />
                    <div class="form-row-3">
                        <x-field name="phone" type="tel" :label="__('web.business.phone')" :value="$business?->phone ?? $member->mobile" maxlength="10" inputmode="numeric" />
                        <x-field name="whatsapp" type="tel" :label="__('web.business.whatsapp_number')" :value="$business?->whatsapp" maxlength="10" inputmode="numeric" />
                        <x-field name="email" type="email" :label="__('web.business.email_label')" :value="$business?->email" maxlength="191" />
                    </div>
                    <fieldset class="field type-fieldset">
                        <legend class="field-label">{{ __('web.business.privacy') }}</legend>
                        <div class="flex flex-wrap gap-4">
                            @foreach (['show_phone', 'show_whatsapp', 'show_email'] as $flag)
                                <label class="checkbox-row"><input type="hidden" name="{{ $flag }}" value="0"><input type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $business?->{$flag} ?? $flag !== 'show_email'))> {{ __('web.business.'.$flag) }}</label>
                            @endforeach
                        </div>
                    </fieldset>
                    <x-field name="website" type="url" :label="__('web.business.website_label')" :value="$business?->website" placeholder="https://" maxlength="255" />

                    <div class="profile-photo-row">
                        @if ($business?->logo)<x-img :image="$business->logo" alt="" class="business-thumb" sizes="120px" />@endif
                        <div class="profile-photo-input">
                            <x-field name="logo" type="file" :label="__('web.business.logo')" :hint="__('web.business.logo_hint')" accept="image/jpeg,image/png,image/webp" />
                            @if ($business?->logo)<label class="checkbox-row"><input type="checkbox" name="remove_logo" value="1"> {{ __('web.business.remove_logo') }}</label>@endif
                        </div>
                    </div>

                    @if ($st === BS::Approved && setting('business_reapproval_on_edit'))
                        <p class="muted register-note">{{ __('web.business.reapproval_note') }}</p>
                    @endif
                    <button type="submit" class="btn btn-primary btn-block"><span class="spinner" aria-hidden="true"></span> {{ __($business && $st === BS::Approved ? 'web.business.save' : 'web.business.submit') }}</button>
                </form>
            @endif
        </div>
    </section>
</x-layouts.member>
