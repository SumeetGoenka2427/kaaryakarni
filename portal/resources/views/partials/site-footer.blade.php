@php
    $link = fn (string $route, string $label) => Route::has($route)
        ? '<li><a href="'.e(localized_route($route)).'">'.e(__($label)).'</a></li>'
        : '';
@endphp
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <x-logo variant="footer" />
                <p>{{ setting_t('tagline') }}</p>
            </div>
            <div>
                <h2 class="footer-heading">{{ __('web.footer.explore') }}</h2>
                <ul class="footer-links">
                    {!! $link('about', 'web.nav.about') !!}
                    {!! $link('business.index', 'web.nav.directory') !!}
                    {!! $link('gallery.index', 'web.nav.gallery') !!}
                </ul>
            </div>
            <div>
                <h2 class="footer-heading">{{ __('web.footer.account') }}</h2>
                <ul class="footer-links">
                    {!! $link('login', 'web.footer.member_login') !!}
                    {!! $link('register', 'web.footer.register') !!}
                    {!! $link('donations.index', 'web.footer.donate') !!}
                    {!! $link('contact', 'web.footer.contact_us') !!}
                </ul>
            </div>
            <div>
                <h2 class="footer-heading">{{ __('web.footer.office') }}</h2>
                <ul class="footer-links">
                    <li>{{ setting_t('address') }}</li>
                    @if ($phone = setting('phone_primary'))
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a></li>
                    @endif
                    @if ($email = setting('email'))
                        <li><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                    @endif
                    @if ($hours = setting_t('office_hours'))
                        <li>{{ $hours }}</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ now()->year }} {{ setting_t('org_name') }}. {{ __('web.footer.rights') }}</span>
            <span>{{ __('web.footer.built_for') }}</span>
        </div>
    </div>
</footer>
