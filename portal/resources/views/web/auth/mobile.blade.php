{{-- Step 1 of login / registration: mobile number (from web/login.html + web/register.html). --}}
@php $isRegister = $purpose === 'register'; @endphp
<x-layouts.web :title="__($isRegister ? 'web.auth.register_title' : 'web.auth.login_title')" noindex>
    <section class="section auth-section">
        <div class="container">
            <div @class(['auth-shell', 'auth-shell-wide' => $isRegister])>
                <div class="text-center auth-head">
                    <h1>{{ __($isRegister ? 'web.auth.register_title' : 'web.auth.login_title') }}</h1>
                    <p class="muted">{{ __($isRegister ? 'web.auth.register_subtitle' : 'web.auth.login_subtitle') }}</p>
                </div>

                @if ($isRegister)
                    <div class="auth-steps" aria-hidden="true"><div class="step is-active"></div><div class="step"></div><div class="step"></div></div>
                @endif

                @foreach (['status' => 'info', 'warning' => 'warning'] as $key => $type)
                    @if (session($key))
                        <div class="form-alert form-alert-{{ $type }}" role="status">{{ session($key) }}</div>
                    @endif
                @endforeach

                <form method="POST" action="{{ localized_route('otp.send') }}" data-validate novalidate>
                    @csrf
                    <input type="hidden" name="purpose" value="{{ $purpose }}">
                    <div @class(['field', 'auth-field', 'has-error' => $errors->has('mobile')])>
                        <label for="mobile">{{ __('web.auth.mobile') }} <span class="required" aria-hidden="true">*</span></label>
                        <input type="tel" id="mobile" name="mobile" value="{{ old('mobile') }}" inputmode="numeric" maxlength="10"
                               autocomplete="tel-national" placeholder="98XXXXXXXX" data-rule="mobile" required autofocus
                               aria-describedby="mobile-hint mobile-error" @error('mobile') aria-invalid="true" @enderror>
                        <span class="hint" id="mobile-hint">{{ __($isRegister ? 'web.auth.mobile_hint_register' : 'web.auth.mobile_hint_login') }}</span>
                        <span class="error-text" id="mobile-error" role="alert">{{ $errors->first('mobile') ?: __('web.auth.mobile_invalid') }}</span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="spinner" aria-hidden="true"></span> {{ __('web.auth.send_otp') }}
                    </button>
                </form>

                <p class="text-center muted auth-footnote">{{ __('web.auth.privacy_note') }}</p>
                <p class="text-center muted auth-switch">
                    @if ($isRegister)
                        {{ __('web.auth.have_account') }} <a href="{{ localized_route('login') }}">{{ __('web.auth.login_here') }}</a>
                    @else
                        {{ __('web.auth.new_here') }} <a href="{{ localized_route('register') }}">{{ __('web.auth.create_profile') }}</a>
                    @endif
                </p>
            </div>
        </div>
    </section>
</x-layouts.web>
