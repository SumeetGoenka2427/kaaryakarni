<div class="topbar">
    <div class="container">
        <div class="topbar-contacts">
            <span class="topbar-tag">{{ setting_t('org_name') }}</span>
            @if ($phone = setting('phone_primary'))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">{{ $phone }}</a>
            @endif
            @if ($email = setting('email'))
                <a href="mailto:{{ $email }}">{{ $email }}</a>
            @endif
        </div>
        <div class="topbar-contacts">
            @if (Route::has('contact'))
                <a href="{{ localized_route('contact') }}">{{ setting_t('office_name') }}</a>
            @else
                <span>{{ setting_t('office_name') }}</span>
            @endif
        </div>
    </div>
</div>
