{{-- Left / right homepage banner panel. Mode is chosen in Admin › Homepage Banners. --}}
@php $mode = $panel?->mode ?? ($position === 'left' ? 'membership_cta' : 'advertisement'); @endphp

@if ($mode === 'advertisement' && $panel?->ad_image)
    @php $adUrl = $panel->safeAdUrl(); @endphp
    <aside class="donation-panel ad-panel" aria-label="{{ __('web.banner.ad_label') }}">
        <span class="panel-label">{{ __('web.banner.ad_label') }}</span>
        <{{ $adUrl ? 'a' : 'div' }} @if ($adUrl) href="{{ $adUrl }}" @if (str_starts_with($adUrl, 'https://')) target="_blank" rel="noopener sponsored" @endif @endif class="ad-creative">
            <x-img :image="$panel->ad_image" :alt="$panel->t('ad_label') ?? __('web.banner.ad_label')" sizes="(max-width: 1024px) 100vw, 22vw" />
            <span class="ad-tag">{{ __('web.banner.ad_tag') }}</span>
        </{{ $adUrl ? 'a' : 'div' }}>
        <p class="muted panel-note">{{ __('web.banner.ad_contact') }} <a href="{{ localized_route('contact') }}">{{ __('web.banner.ad_contact_link') }}</a>.</p>
    </aside>
@elseif ($mode === 'advertisement')
    {{-- Advertisement slot without a creative yet: invite advertisers instead of an empty box. --}}
    <aside class="donation-panel ad-panel" aria-label="{{ __('web.banner.ad_label') }}">
        <span class="panel-label">{{ __('web.banner.ad_label') }}</span>
        <h3>{{ $panel?->t('ad_label') ?: __('web.banner.ad_contact') }}</h3>
        <p class="muted panel-note">{{ setting_t('org_name') }}</p>
        <a href="{{ localized_route('contact') }}" class="btn btn-outline btn-sm btn-block">{{ __('web.banner.ad_contact_link') }}</a>
    </aside>
@else
    <aside class="donation-panel" aria-label="{{ __('web.banner.membership_label') }}">
        <span class="panel-label">{{ __('web.banner.membership_label') }}</span>
        <h3>{{ __('web.banner.membership_title') }}</h3>
        <p class="muted panel-note">{{ __('web.banner.membership_text') }}</p>
        <div class="meta-row"><span>{{ __('web.banner.membership_fee') }}</span><strong class="panel-amount">{{ inr(setting('membership_fee')) }}</strong></div>
        <a href="{{ localized_route('register') }}" class="btn btn-primary btn-sm btn-block">{{ __('web.banner.membership_cta') }}</a>
    </aside>
@endif
