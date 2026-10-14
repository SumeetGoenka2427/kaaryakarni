{{-- Public website layout: topbar + header + footer (from web/index.html). --}}
@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'noindex' => false,
])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo :title="$title" :description="$description" :image="$image" :noindex="$noindex" />
    @vite(['resources/css/web.css', 'resources/js/web.js'])
    @include('partials.head-brand')
    {{ $head ?? '' }}
</head>
<body>
    <a href="#main" class="skip-link">{{ __('web.skip_link') }}</a>

    @include('partials.topbar')
    @include('partials.site-header')

    <main id="main">
        <x-flash />
        {{ $slot }}
    </main>

    @include('partials.site-footer')
</body>
</html>
