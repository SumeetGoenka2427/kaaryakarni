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
                @php
                    $socials = array_filter([
                        'facebook' => safe_url(setting('social_facebook')),
                        'instagram' => safe_url(setting('social_instagram')),
                        'youtube' => safe_url(setting('social_youtube')),
                    ]);
                @endphp
                @if ($socials)
                    <ul class="footer-social" aria-label="{{ __('web.footer.social') }}">
                        @foreach ($socials as $network => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                                    @switch($network)
                                        @case('facebook')<path d="M14 8h3V4h-3c-2.8 0-4 1.8-4 4.3V10H7v4h3v8h4v-8h3l1-4h-4V8.6c0-.4.2-.6.6-.6z"/>@break
                                        @case('instagram')<path d="M12 7.3A4.7 4.7 0 1 0 12 16.7 4.7 4.7 0 0 0 12 7.3zm0 7.7a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6-7.9a1.1 1.1 0 1 1-2.2 0 1.1 1.1 0 0 1 2.2 0zM12 4c2.2 0 2.4 0 3.3.1 2.2.1 3.3 1.2 3.5 3.5.1.9.1 1.1.1 3.3v2.2c0 2.2 0 2.4-.1 3.3-.2 2.3-1.3 3.4-3.5 3.5-.9.1-1.1.1-3.3.1h-.1c-2.2 0-2.4 0-3.3-.1-2.2-.1-3.4-1.2-3.5-3.5C5 15.4 5 15.2 5 13v-2.2c0-2.1 0-2.4.1-3.3.1-2.2 1.3-3.4 3.5-3.5C9.6 4 9.8 4 12 4z"/>@break
                                        @default<path d="M21.6 7.2a2.6 2.6 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.6 2.6 0 0 0 2.4 7.2 27 27 0 0 0 2 12a27 27 0 0 0 .4 4.8 2.6 2.6 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.6 2.6 0 0 0 1.8-1.8A27 27 0 0 0 22 12a27 27 0 0 0-.4-4.8zM10 15V9l5.2 3z"/>
                                    @endswitch
                                </svg>
                            </a></li>
                        @endforeach
                    </ul>
                @endif
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
