{{-- Favicons and optional admin theme colours. Included in every layout's <head>. --}}
@php
    $branding = app(\App\Services\Branding\BrandingService::class);
    $colors = array_filter([
        '--color-navy-800' => setting('color_primary'),
        '--color-gold-500' => setting('color_gold'),
        '--color-saffron-500' => setting('color_accent'),
    ], fn ($v) => is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v));
@endphp
@if ($icon = $branding->faviconUrl(32))
<link rel="icon" type="image/png" sizes="32x32" href="{{ $icon }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $branding->faviconUrl(180) }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ $branding->faviconUrl(192) }}">
@endif
<meta name="theme-color" content="{{ setting('color_primary') ?: '#0a1f3d' }}">
@if ($colors)
<style>:root{@foreach ($colors as $var => $value){{ $var }}:{{ $value }};@endforeach}</style>
@endif
