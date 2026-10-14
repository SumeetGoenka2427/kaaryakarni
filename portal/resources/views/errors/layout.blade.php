{{--
    Error page body. 404s never reach route middleware, so the language is
    detected from the URL here (/hi/... → Hindi).
--}}
@php
    if (request()->segment(1) === 'hi') {
        app()->setLocale('hi');
    }
    $code = $code ?? 500;
    $minimal = in_array($code, [500, 503], true);
@endphp
@if ($minimal)
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ __("web.errors.{$code}_title") }} — {{ __('web.org.name') }}</title>
    @vite(['resources/css/web.css'])
</head>
<body>
    <main id="main" class="section">
        <div class="container text-center">
            <x-logo variant="header" class="error-logo" />
            @include('errors.body')
        </div>
    </main>
</body>
</html>
@else
<x-layouts.web :title="__('web.errors.'.$code.'_title')" noindex>
    <section class="section">
        <div class="container">
            @include('errors.body')
        </div>
    </section>
</x-layouts.web>
@endif
