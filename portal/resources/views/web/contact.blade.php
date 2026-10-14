{{-- Contact (web/contact.html). Office details: Admin › Settings. Messages: Admin › Contact Messages. --}}
<x-layouts.web :title="__('web.nav.contact')" :description="__('web.contact.meta_description')">
    <x-page-hero :title="__('web.contact.hero_title')" :subtitle="__('web.contact.hero_subtitle')"
                 :breadcrumbs="[__('web.nav.contact') => null]" />

    <section class="section">
        <div class="container">
            <div class="grid grid-2 align-start">
                <div>
                    <h2>{{ __('web.contact.form_heading') }}</h2>
                    <form method="POST" action="{{ localized_route('contact.store') }}" class="form-grid" data-validate novalidate>
                        @csrf
                        <div class="form-row">
                            <x-field name="name" :label="__('web.contact.name')" :placeholder="__('web.contact.name_placeholder')" required maxlength="120" autocomplete="name" data-rule="required" />
                            <x-field name="mobile" type="tel" :label="__('web.contact.mobile')" placeholder="98XXXXXXXX" required maxlength="10" inputmode="numeric" autocomplete="tel-national" data-rule="mobile" />
                        </div>
                        <x-field name="email" type="email" :label="__('web.contact.email')" placeholder="you@example.com" maxlength="191" autocomplete="email" data-rule="email" />
                        <x-field name="subject" type="select" :label="__('web.contact.subject')" :options="$subjects" required />
                        <x-field name="message" type="textarea" :rows="4" :label="__('web.contact.message')" :placeholder="__('web.contact.message_placeholder')" required maxlength="3000" data-rule="required" />
                        {{-- Honeypot for spam bots: hidden from people and screen readers. --}}
                        <div class="hp-field" aria-hidden="true">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><span class="spinner" aria-hidden="true"></span> {{ __('web.contact.send') }}</button>
                    </form>
                </div>
                <div>
                    <h2>{{ __('web.contact.office_heading') }}</h2>
                    <div class="stack">
                        @if ($address = setting_t('address'))
                            <div class="card"><div class="card-body">
                                <h3 class="info-title">{{ __('web.contact.address') }}</h3>
                                <p class="info-text">{{ $address }}</p>
                                @if ($map = safe_url(setting('map_url')))
                                    <a href="{{ $map }}" target="_blank" rel="noopener" class="info-link">{{ __('web.contact.map') }} &rarr;</a>
                                @endif
                            </div></div>
                        @endif
                        @if (setting('phone_primary') || setting('phone_secondary'))
                            <div class="card"><div class="card-body">
                                <h3 class="info-title">{{ __('web.contact.phone') }}</h3>
                                @foreach (array_filter([setting('phone_primary'), setting('phone_secondary')]) as $phone)
                                    <p class="info-text"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></p>
                                @endforeach
                            </div></div>
                        @endif
                        @if ($email = setting('email'))
                            <div class="card"><div class="card-body">
                                <h3 class="info-title">{{ __('web.contact.email_label') }}</h3>
                                <p class="info-text"><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                            </div></div>
                        @endif
                        @if ($hours = setting_t('office_hours'))
                            <div class="card"><div class="card-body">
                                <h3 class="info-title">{{ __('web.contact.hours') }}</h3>
                                <p class="info-text">{{ $hours }}</p>
                            </div></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.web>
