{{-- Empty / error / permission state block (design: .state-block). Icon is decorative SVG, not emoji. --}}
@props(['title', 'icon' => 'inbox'])
<div {{ $attributes->class('state-block') }} role="status">
    <div class="state-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            @switch($icon)
                @case('image')
                    <rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 17l-5-5-4 4-3-3-4 4"/>
                    @break
                @case('search')
                    <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                    @break
                @case('lock')
                    <rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    @break
                @case('alert')
                    <circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/>
                    @break
                @default
                    <path d="M4 13h4l2 3h4l2-3h4"/><path d="M5 13l2-8h10l2 8v6H5z"/>
            @endswitch
        </svg>
    </div>
    <h3>{{ $title }}</h3>
    @if ($slot->isNotEmpty())
        <div class="state-text">{{ $slot }}</div>
    @endif
</div>
