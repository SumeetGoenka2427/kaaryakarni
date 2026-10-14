{{--
    Site logo. One component for every placement so Admin → Branding (P03)
    changes the logo everywhere at once.
    variant: header | footer | member | admin
--}}
@props(['variant' => 'header', 'href' => null])
@php
    $href ??= localized_route('home');
    $subtitle = match ($variant) {
        'member' => __('web.org.member_area'),
        default => __('web.org.portal'),
    };
@endphp
@if ($variant === 'admin')
    <div class="sidebar-brand">
        <span class="brand-mark" aria-hidden="true">KS</span>
        <span><strong>Samaj Admin</strong><span>{{ __('web.org.name', [], 'en') }}</span></span>
    </div>
@else
    <a href="{{ $href }}" {{ $attributes->class(['brand', 'brand-on-dark' => $variant === 'footer']) }}>
        <span class="brand-mark" aria-hidden="true">KS</span>
        <span class="brand-text">
            <strong>{{ __('web.org.short') }}</strong>
            <span>{{ $subtitle }}</span>
        </span>
    </a>
@endif
