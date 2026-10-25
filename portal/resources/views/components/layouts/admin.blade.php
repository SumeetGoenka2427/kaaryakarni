{{-- Admin panel layout: dark sidebar + topbar (from admin/*.html). English only. --}}
@props(['title' => 'Dashboard'])
@php
    $admin = config('auth.guards.admin') ? auth('admin')->user() : null;
    $initials = $admin ? collect(explode(' ', $admin->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') : '';
    $canSee = fn (array $item) => Route::has($item['route'])
        && (! isset($item['can']) || ($admin && $admin->can($item['can'])));
    $badges = $admin ? app(\App\Services\Reports\AdminNavBadges::class)->all() : [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} — Samaj Admin Panel</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @include('partials.head-brand')
    {{ $head ?? '' }}
</head>
<body class="admin-body">
<div class="admin-shell">
    <div class="admin-scrim" data-admin-scrim></div>
    <aside class="admin-sidebar" id="admin-sidebar" data-admin-sidebar>
        <x-logo variant="admin" />
        <nav aria-label="Admin">
            <ul class="admin-nav">
                @foreach (config('navigation.admin') as $group => $items)
                    @php $visible = array_filter($items, $canSee); @endphp
                    @continue(empty($visible))
                    <li class="nav-group">
                        <span class="nav-group-label" id="nav-{{ \Illuminate\Support\Str::slug($group) }}">{{ $group }}</span>
                        <ul aria-labelledby="nav-{{ \Illuminate\Support\Str::slug($group) }}">
                            @foreach ($visible as $item)
                                @php $count = isset($item['badge']) ? ($badges[$item['badge']] ?? 0) : 0; @endphp
                                <li>
                                    <a href="{{ route($item['route']) }}" @if (request()->routeIs(...($item['active'] ?? [$item['route']]))) aria-current="page" @endif>
                                        <x-admin.nav-icon :name="$item['icon'] ?? 'default'" />
                                        <span class="nav-text">{{ $item['label'] }}</span>
                                        @if ($count)<span class="nav-count" title="{{ $count }} waiting">{{ $count > 99 ? '99+' : $count }}<span class="visually-hidden"> waiting</span></span>@endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="{{ route('home') }}" class="sidebar-public-link">&larr; View Public Site</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Toggle admin menu">&#9776;</button>
                <h1>{{ $title }}</h1>
            </div>
            @if ($admin)
                <div class="admin-user-chip">
                    <span class="admin-user-name">{{ $admin->name }}</span>
                    <span class="avatar" aria-hidden="true">{{ strtoupper($initials) }}</span>
                    @if (Route::has('admin.logout'))
                        <form method="POST" action="{{ route('admin.logout') }}" data-confirm="You will be signed out of the admin panel.">
                            @csrf
                            <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                        </form>
                    @endif
                </div>
            @endif
        </header>
        <main class="admin-content" id="main">
            <x-flash />
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
