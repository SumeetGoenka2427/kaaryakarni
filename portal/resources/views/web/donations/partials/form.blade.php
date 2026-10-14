{{--
    Donate form (web/donations.html). Works for guests and members (prefilled).
    $selected = preselected campaign, $ticket = start on "buy ticket".
--}}
@php
    $member = auth('member')->user();
    $isTicket = old('type', $ticket && $selected?->sellsTickets() ? 'ticket' : 'donation') === 'ticket';
    $offlineFirst = ! $onlineAvailable || old('mode') === 'offline';
    $presets = \App\Http\Controllers\Web\DonationController::PRESETS;
@endphp
<form method="POST" action="{{ localized_route('donations.store') }}" enctype="multipart/form-data" class="form-grid donate-form" id="donate" data-donate-form novalidate>
    @csrf
    @if ($errors->any())
        <div class="form-alert form-alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <x-field name="campaign" type="select" :label="__('web.donations.campaign')" required data-donate-campaign
             :options="$campaigns->mapWithKeys(fn ($c) => [$c->slug => $c->t('title')])->all()" :value="$selected?->slug" />

    {{-- Ticket option for campaigns that sell tickets (shown/hidden by JS per campaign) --}}
    <fieldset class="field type-fieldset" data-ticket-block @unless ($campaigns->contains(fn ($c) => $c->sellsTickets())) hidden @endunless>
        <legend class="field-label">{{ __('web.donations.type') }}</legend>
        <div class="type-options">
            <label class="checkbox-row"><input type="radio" name="type" value="donation" @checked(! $isTicket) data-type-radio> {{ __('web.donations.type_donation') }}</label>
            @foreach ($campaigns->filter(fn ($c) => $c->sellsTickets()) as $c)
                <label class="checkbox-row" data-ticket-option="{{ $c->slug }}" @if ($selected?->slug !== $c->slug) hidden @endif>
                    <input type="radio" name="type" value="ticket" @checked($isTicket && $selected?->slug === $c->slug) data-type-radio data-price="{{ (float) $c->ticket_price }}">
                    {{ __('web.donations.type_ticket', ['label' => $c->t('ticket_label') ?: __('web.donations.buy_ticket')]) }} — {{ __('web.donations.ticket_price', ['price' => inr($c->ticket_price)]) }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <div data-amount-block @if ($isTicket) hidden @endif>
        <span class="field-label">{{ __('web.donations.amount') }} <span class="required" aria-hidden="true">*</span></span>
        <div class="amount-presets" role="group" aria-label="{{ __('web.donations.amount') }}">
            @foreach ($presets as $p)
                <button type="button" class="btn btn-outline btn-sm" data-amount-preset="{{ $p }}">{{ inr($p) }}</button>
            @endforeach
        </div>
        <x-field name="amount" type="number" :label="__('web.donations.amount_other')" min="1" max="1000000" step="1" inputmode="numeric" placeholder="1001" data-amount-input />
    </div>
    <div data-quantity-block @unless ($isTicket) hidden @endunless>
        <x-field name="quantity" type="number" :label="__('web.donations.quantity')" :value="old('quantity', 1)" min="1" max="20" :hint="__('web.donations.ticket_note')" />
    </div>

    <div class="form-row">
        <x-field name="donor_name" :label="__('web.donations.name')" :value="$member?->name" required maxlength="120" autocomplete="name" data-rule="required" />
        <x-field name="donor_mobile" type="tel" :label="__('web.donations.mobile')" :value="$member?->mobile" required maxlength="10" inputmode="numeric" autocomplete="tel-national" data-rule="mobile" :hint="__('web.donations.mobile_hint')" />
    </div>
    <div class="form-row">
        <x-field name="donor_email" type="email" :label="__('web.donations.email')" :value="$member?->email" maxlength="191" autocomplete="email" />
        <x-field name="donor_pan" :label="__('web.donations.pan')" maxlength="10" :hint="__('web.donations.pan_hint')" autocapitalize="characters" />
    </div>
    <x-field name="message" type="textarea" :rows="2" :label="__('web.donations.message')" maxlength="500" />
    <label class="checkbox-row"><input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous'))> {{ __('web.donations.anonymous_label') }}</label>

    <div class="field">
        <span class="field-label">{{ __('web.pay.mode') }}</span>
        <div class="flex gap-3" data-payment-toggle role="group" aria-label="{{ __('web.pay.mode') }}">
            <button type="button" @class(['btn', 'btn-sm', 'btn-primary' => ! $offlineFirst, 'btn-outline' => $offlineFirst]) data-mode="online" aria-pressed="{{ $offlineFirst ? 'false' : 'true' }}">{{ __('web.pay.online') }}</button>
            <button type="button" @class(['btn', 'btn-sm', 'btn-primary' => $offlineFirst, 'btn-outline' => ! $offlineFirst]) data-mode="offline" aria-pressed="{{ $offlineFirst ? 'true' : 'false' }}">{{ __('web.pay.offline') }}</button>
        </div>
        <input type="hidden" name="mode" value="{{ $offlineFirst ? 'offline' : 'online' }}" data-mode-input>
    </div>

    <div data-mode-panel="online" @if ($offlineFirst) hidden @endif>
        @if ($onlineAvailable)
            <div class="form-alert form-alert-info">{{ __('web.donations.online_note') }}</div>
            <button type="submit" class="btn btn-primary btn-block"><span class="spinner" aria-hidden="true"></span> {{ __('web.donations.submit_online') }}</button>
        @else
            <div class="form-alert form-alert-warning" role="status">{{ __('web.pay.online_unavailable') }}</div>
        @endif
    </div>
    <div data-mode-panel="offline" class="form-grid" @unless ($offlineFirst) hidden @endunless>
        <div class="form-alert form-alert-info">{{ __('web.donations.offline_note') }}</div>
        <div class="form-row">
            <x-field name="offline_method" type="select" :label="__('web.pay.method')" :options="\App\Enums\OfflineMethod::options()" />
            <x-field name="offline_paid_on" type="date" :label="__('web.pay.paid_on')" :value="now()->toDateString()" :max="now()->toDateString()" />
        </div>
        <x-field name="offline_reference" :label="__('web.pay.reference')" :hint="__('web.pay.reference_hint')" maxlength="100" />
        <x-field name="proof" type="file" :label="__('web.pay.proof')" :hint="__('web.pay.proof_hint')" accept="image/jpeg,image/png,image/webp,application/pdf" />
        <button type="submit" class="btn btn-primary btn-block"><span class="spinner" aria-hidden="true"></span> {{ __('web.donations.submit_offline') }}</button>
    </div>
    <p class="muted secure-note"><span aria-hidden="true">&#128274;</span> {{ __('web.pay.secure_note') }}</p>
</form>
