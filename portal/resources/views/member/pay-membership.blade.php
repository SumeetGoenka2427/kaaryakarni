{{-- Pay Membership Fee (web/member/pay-membership.html). Online = Razorpay, Offline = admin verifies. --}}
@php $offlineFirst = ! $onlineAvailable || old('mode') === 'offline' || $errors->hasAny(['offline_method', 'offline_reference', 'offline_paid_on', 'proof']); @endphp
<x-layouts.member :title="__('web.pay.title')">
    <section class="section section-tight">
        <div class="container container-narrow-form">
            <h1>{{ __('web.pay.title') }}</h1>
            <p class="muted">{{ __('web.pay.intro') }}</p>

            <div class="card content-gap">
                <div class="card-body detail-rows">
                    <div class="flex-between"><span class="muted">{{ __('web.pay.member') }}</span><strong>{{ $member->name }}</strong></div>
                    <div class="flex-between"><span class="muted">{{ __('web.my_membership.type') }}</span><strong>{{ __('web.my_membership.type_value') }}</strong></div>
                    <div class="flex-between"><span class="muted">{{ __('web.pay.amount') }}</span><strong class="donation-amount">{{ inr($membership->fee_amount) }}</strong></div>
                </div>
            </div>

            <div class="field content-gap">
                <span class="field-label">{{ __('web.pay.mode') }}</span>
                <div class="flex gap-3" data-payment-toggle role="group" aria-label="{{ __('web.pay.mode') }}">
                    <button type="button" @class(['btn', 'btn-sm', 'btn-primary' => ! $offlineFirst, 'btn-outline' => $offlineFirst]) data-mode="online" aria-pressed="{{ $offlineFirst ? 'false' : 'true' }}">{{ __('web.pay.online') }}</button>
                    <button type="button" @class(['btn', 'btn-sm', 'btn-primary' => $offlineFirst, 'btn-outline' => ! $offlineFirst]) data-mode="offline" aria-pressed="{{ $offlineFirst ? 'true' : 'false' }}">{{ __('web.pay.offline') }}</button>
                </div>
            </div>

            {{-- Online --}}
            <div data-mode-panel="online" @if ($offlineFirst) hidden @endif>
                @if ($onlineAvailable)
                    <div class="form-alert form-alert-info">{{ __('web.pay.online_note') }}</div>
                    <form method="POST" action="{{ localized_route('member.membership.pay.store') }}">
                        @csrf
                        <input type="hidden" name="mode" value="online">
                        <button type="submit" class="btn btn-primary btn-block"><span class="spinner" aria-hidden="true"></span> {{ __('web.pay.pay_online', ['amount' => inr($membership->fee_amount)]) }}</button>
                    </form>
                @else
                    <div class="form-alert form-alert-warning" role="status">{{ __('web.pay.online_unavailable') }}</div>
                @endif
            </div>

            {{-- Offline --}}
            <div data-mode-panel="offline" @unless ($offlineFirst) hidden @endunless>
                <div class="form-alert form-alert-info">{{ __('web.pay.offline_note') }}</div>
                <form method="POST" action="{{ localized_route('member.membership.pay.store') }}" enctype="multipart/form-data" class="form-grid" novalidate>
                    @csrf
                    <input type="hidden" name="mode" value="offline">
                    <div class="form-row">
                        <x-field name="offline_method" type="select" :label="__('web.pay.method')" :options="\App\Enums\OfflineMethod::options()" required />
                        <x-field name="offline_paid_on" type="date" :label="__('web.pay.paid_on')" :value="now()->toDateString()" :max="now()->toDateString()" required />
                    </div>
                    <x-field name="offline_reference" :label="__('web.pay.reference')" :hint="__('web.pay.reference_hint')" maxlength="100" />
                    <x-field name="proof" type="file" :label="__('web.pay.proof')" :hint="__('web.pay.proof_hint')" accept="image/jpeg,image/png,image/webp,application/pdf" />
                    <button type="submit" class="btn btn-primary btn-block"><span class="spinner" aria-hidden="true"></span> {{ __('web.pay.submit_offline') }}</button>
                </form>
            </div>

            <p class="muted secure-note"><span aria-hidden="true">&#128274;</span> {{ __('web.pay.secure_note') }}</p>
        </div>
    </section>
</x-layouts.member>
