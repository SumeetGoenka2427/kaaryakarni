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
                <p>{{ __('web.org.tagline') }}</p>
            </div>
            <div>
                <h2 class="footer-heading">{{ __('web.footer.explore') }}</h2>
                <ul class="footer-links">
                    {!! $link('about', 'web.nav.about') !!}
                    {!! $link('membership', 'web.nav.membership') !!}
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
                    <li>{{ __('web.org.address') }}</li>
                    <li><a href="tel:+911411234567">+91 141 123 4567</a></li>
                    <li><a href="mailto:info@khandelwalsamajjaipur.org">info@khandelwalsamajjaipur.org</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ now()->year }} {{ __('web.org.name') }}. {{ __('web.footer.rights') }}</span>
            <span>{{ __('web.footer.built_for') }}</span>
        </div>
    </div>
</footer>
