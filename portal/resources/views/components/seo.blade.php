@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'noindex' => false,
])
@php
    $siteTitle = __('web.meta.default_title');
    $fullTitle = $title ? $title.' — '.setting_t('org_name') : $siteTitle;
    $description = $description ?: __('web.meta.default_description');
    $locales = array_keys(config('portal.locales'));
    $image ??= app(\App\Services\Branding\BrandingService::class)->url('og_image');
@endphp
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
@if ($noindex)
<meta name="robots" content="noindex, nofollow">
@else
<link rel="canonical" href="{{ url()->current() }}">
@foreach ($locales as $locale)
<link rel="alternate" hreflang="{{ $locale }}" href="{{ switch_locale_url($locale) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ switch_locale_url($locales[0]) }}">
@endif
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ setting_t('org_name') }}">
<meta property="og:title" content="{{ $title ?: $siteTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'hi' ? 'hi_IN' : 'en_IN' }}">
@if ($image)
<meta property="og:image" content="{{ url($image) }}">
@endif
<meta name="twitter:card" content="summary_large_image">
