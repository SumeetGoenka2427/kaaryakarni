{{-- Step 2: OTP entry (from web/login.html "otp" step). --}}
@php $isRegister = $purpose === 'register'; $length = config('portal.otp.length'); @endphp
<x-layouts.web :title="__($isRegister ? 'web.auth.register_title' : 'web.auth.login_title')" noindex>
    <section class="section auth-section">
        <div class="container">
            <div @class(['auth-shell', 'auth-shell-wide' => $isRegister])>
                <div class="text-center auth-head">
                    <h1>{{ __($isRegister ? 'web.auth.register_title' : 'web.auth.login_title') }}</h1>
                    <p class="muted">{{ __('web.auth.otp_sent_to', ['mobile' => $maskedMobile, 'minutes' => config('portal.otp.ttl_minutes')]) }}</p>
                </div>

                @if ($isRegister)
                    <div class="auth-steps" aria-hidden="true"><div class="step is-done"></div><div class="step is-active"></div><div class="step"></div></div>
                @endif

                @foreach (['status' => 'info', 'warning' => 'warning'] as $key => $type)
                    @if (session($key))
                        <div class="form-alert form-alert-{{ $type }}" role="status">{{ session($key) }}</div>
                    @endif
                @endforeach

                <form method="POST" action="{{ localized_route('otp.check') }}" novalidate>
                    @csrf
                    <fieldset class="field auth-field otp-fieldset">
                        <legend>{{ __('web.auth.otp_label') }} <span class="required" aria-hidden="true">*</span></legend>
                        <div class="otp-inputs">
                            @for ($i = 1; $i <= $length; $i++)
                                <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="{{ __('web.auth.otp_digit', ['n' => $i]) }}"
                                       @if ($i === 1) autocomplete="one-time-code" autofocus @else autocomplete="off" @endif>
                            @endfor
                        </div>
                        <input type="hidden" name="otp" value="">
                    </fieldset>

                    @error('otp')
                        <p class="form-alert form-alert-danger otp-error" role="alert">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="spinner" aria-hidden="true"></span> {{ __($isRegister ? 'web.auth.verify_register' : 'web.auth.verify_login') }}
                    </button>
                </form>

                <div class="resend-row auth-resend">
                    <form method="POST" action="{{ localized_route('otp.restart') }}">
                        @csrf
                        <button type="submit" class="link-button">{{ __('web.auth.change_number') }}</button>
                    </form>
                    <form method="POST" action="{{ localized_route('otp.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" data-resend data-seconds="{{ $resendIn }}" @disabled($resendIn > 0)>
                            {{ __('web.auth.resend') }}<span data-resend-timer>@if ($resendIn > 0) ({{ $resendIn }}s)@endif</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-layouts.web>
