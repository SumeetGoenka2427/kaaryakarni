{{-- Member area layout: member header with "More" dropdown (from web/member/*.html). --}}
@props(['title' => null])
@php
    $items = collect(config('navigation.member.main'))->filter(fn ($i) => Route::has($i['route']));
    $more = collect(config('navigation.member.more'))->filter(fn ($i) => Route::has($i['route']));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-seo :title="$title" noindex />
    @vite(['resources/css/web.css', 'resources/js/web.js'])
    @include('partials.head-brand')
    {{ $head ?? '' }}
</head>
<body>
    <a href="#main" class="skip-link">{{ __('web.skip_link') }}</a>

    <header class="site-header">
        <div class="container">
            <x-logo variant="member" :href="Route::has('member.dashboard') ? localized_route('member.dashboard') : null" />
            <button class="nav-toggle" data-nav-toggle aria-expanded="false" aria-label="{{ __('web.nav.toggle') }}" aria-controls="main-nav">
                <span></span><span></span><span></span>
            </button>
            <nav class="main-nav" id="main-nav" data-main-nav aria-label="{{ __('web.member_nav.label') }}">
                <ul class="nav-links">
                    @foreach ($items as $item)
                        <li><a href="{{ localized_route($item['route']) }}" @if (route_is_current($item['route'].'*')) aria-current="page" @endif>{{ __($item['label']) }}</a></li>
                    @endforeach
                    @if ($more->isNotEmpty())
                        <li class="nav-dropdown" data-nav-dropdown>
                            <button type="button" class="nav-dropdown-toggle @if ($more->contains(fn ($i) => route_is_current($i['route'].'*'))) is-active @endif" data-nav-dropdown-toggle aria-haspopup="true" aria-expanded="false">
                                {{ __('web.nav.more') }} <span class="nav-dropdown-caret" aria-hidden="true">&#9662;</span>
                            </button>
                            <ul class="nav-dropdown-menu" data-nav-dropdown-menu hidden>
                                @foreach ($more as $item)
                                    <li><a href="{{ localized_route($item['route']) }}" @if (route_is_current($item['route'].'*')) aria-current="page" @endif>{{ __($item['label']) }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                </ul>
                <div class="nav-cta">
                    <x-lang-toggle />
                    <a href="{{ localized_route('home') }}" class="btn btn-outline btn-sm">{{ __('web.nav.public_site') }}</a>
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ localized_route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('web.nav.logout') }}</button>
                        </form>
                    @endif
                </div>
            </nav>
        </div>
    </header>

    <main id="main">
        <x-flash />
        {{ $slot }}
    </main>
</body>
</html>
