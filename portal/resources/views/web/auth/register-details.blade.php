{{-- Step 3: member details (from web/register.html profile step). No city/state: Jaipur area only. --}}
<x-layouts.web :title="__('web.register.details_title')" noindex>
    <section class="section auth-section">
        <div class="container">
            <div class="auth-shell auth-shell-wide">
                <div class="text-center auth-head">
                    <h1>{{ __('web.register.details_title') }}</h1>
                    <p class="muted">{{ __('web.auth.register_subtitle') }}</p>
                </div>
                <div class="auth-steps" aria-hidden="true"><div class="step is-done"></div><div class="step is-done"></div><div class="step is-active"></div></div>

                <div class="form-alert form-alert-success" role="status">{{ __('web.register.verified', ['mobile' => $mobile]) }}</div>

                <form method="POST" action="{{ localized_route('register.store') }}" class="form-grid" novalidate data-validate>
                    @csrf
                    <div class="form-row">
                        <x-field name="name" :label="__('web.register.full_name')" :placeholder="__('web.register.full_name_placeholder')" required autocomplete="name" maxlength="120" data-rule="required" />
                        <x-field name="father_or_husband_name" :label="__('web.register.father_name')" maxlength="120" />
                    </div>
                    <div class="form-row">
                        <x-field name="dob" type="date" :label="__('web.register.dob')" :max="now()->subDay()->toDateString()" />
                        <x-field name="gender" type="select" :label="__('web.register.gender')" :placeholder="__('web.register.select')" :options="\App\Enums\Gender::options()" />
                    </div>
                    <x-field name="email" type="email" :label="__('web.register.email')" placeholder="you@example.com" autocomplete="email" maxlength="191" />
                    <x-field name="area_id" type="select" :label="__('web.register.area')" :placeholder="__('web.register.area_placeholder')"
                             :options="$areas" :hint="__('web.register.area_hint')" required data-rule="required" />
                    <x-field name="address" type="textarea" :rows="2" :label="__('web.register.address')" :placeholder="__('web.register.address_placeholder')" maxlength="500" />

                    <div @class(['field', 'has-error' => $errors->has('consent')])>
                        <label class="checkbox-row">
                            <input type="checkbox" name="consent" value="1" @checked(old('consent')) required>
                            <span>{{ __('web.register.consent') }}</span>
                        </label>
                        @error('consent')<span class="error-text" role="alert">{{ $message }}</span>@enderror
                    </div>

                    <p class="muted register-note">{{ __('web.register.family_note') }}</p>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="spinner" aria-hidden="true"></span> {{ __('web.register.submit') }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.web>
