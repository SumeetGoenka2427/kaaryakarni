{{--
    Site logo — one component for every placement, so Admin → Branding
    changes it everywhere at once. Falls back to the design's "KS" mark.
    variant: header | footer | member | admin
--}}
@props(['variant' => 'header', 'href' => null])
@php
    $branding = app(\App\Services\Branding\BrandingService::class);
    // Dark backgrounds (footer, admin sidebar) use the light logo, else the header logo.
    $logo = in_array($variant, ['footer', 'admin'], true)
        ? ($branding->get('logo_footer') ?? $branding->get('logo_header'))
        : $branding->get('logo_header');
    $showName = ! $logo || setting('show_name_beside_logo');
    $href ??= localized_route('home');
    $subtitle = $variant === 'member' ? __('web.org.member_area') : __('web.org.portal');
@endphp
@if ($variant === 'admin')
    <div class="sidebar-brand">
        @if ($logo)
            <img src="{{ $logo->url() }}" width="{{ $logo->width }}" height="{{ $logo->height }}" alt="" class="brand-logo">
        @else
            <span class="brand-mark" aria-hidden="true">KS</span>
        @endif
        <span><strong>Samaj Admin</strong><span>{{ setting('org_name_en') }}</span></span>
    </div>
@else
    <a href="{{ $href }}" {{ $attributes->class(['brand', 'brand-on-dark' => $variant === 'footer']) }}>
        @if ($logo)
            <img src="{{ $logo->url() }}" width="{{ $logo->width }}" height="{{ $logo->height }}"
                 alt="{{ $showName ? '' : setting_t('org_name') }}" class="brand-logo" @if ($variant === 'header') fetchpriority="high" @endif>
        @else
            <span class="brand-mark" aria-hidden="true">KS</span>
        @endif
        @if ($showName)
            <span class="brand-text">
                <strong>{{ setting_t('org_short_name') }}</strong>
                <span>{{ $subtitle }}</span>
            </span>
        @endif
    </a>
@endif
