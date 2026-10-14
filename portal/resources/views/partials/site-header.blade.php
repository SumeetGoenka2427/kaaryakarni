@php
    $member = config('auth.guards.member') ? auth('member')->user() : null;
@endphp
<header class="site-header">
    <div class="container">
        <x-logo variant="header" />
        <button class="nav-toggle" data-nav-toggle aria-expanded="false" aria-label="{{ __('web.nav.toggle') }}" aria-controls="main-nav">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" id="main-nav" data-main-nav aria-label="{{ __('web.nav.primary') }}">
            <ul class="nav-links">
                @foreach (config('navigation.public') as $item)
                    @continue(! Route::has($item['route']))
                    <li>
                        <a href="{{ localized_route($item['route']) }}"
                           @if (route_is_current(...$item['active'])) aria-current="page" @endif>{{ __($item['label']) }}</a>
                    </li>
                @endforeach
            </ul>
            <div class="nav-cta">
                <x-lang-toggle />
                @if ($member && Route::has('member.dashboard'))
                    <a href="{{ localized_route('member.dashboard') }}" class="btn btn-gold btn-sm">{{ __('web.nav.dashboard') }}</a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ localized_route('login') }}" class="btn btn-outline btn-sm">{{ __('web.nav.login') }}</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ localized_route('register') }}" class="btn btn-gold btn-sm">{{ __('web.nav.join') }}</a>
                    @endif
                @endif
            </div>
        </nav>
    </div>
</header>
