<div class="topbar">
    <div class="container">
        <div class="topbar-contacts">
            <span class="topbar-tag">{{ __('web.org.name') }}</span>
            <a href="tel:+911411234567">+91 141 123 4567</a>
            <a href="mailto:info@khandelwalsamajjaipur.org">info@khandelwalsamajjaipur.org</a>
        </div>
        <div class="topbar-contacts">
            @if (Route::has('contact'))
                <a href="{{ localized_route('contact') }}">{{ __('web.org.address_short') }}</a>
            @else
                <span>{{ __('web.org.address_short') }}</span>
            @endif
        </div>
    </div>
</div>
