{{-- Centered section heading with gold eyebrow (design: .section-head). --}}
@props(['title', 'eyebrow' => null, 'text' => null, 'level' => 2])
<div {{ $attributes->class('section-head') }}>
    @if ($eyebrow)
        <span class="eyebrow">{{ $eyebrow }}</span>
    @endif
    <h{{ $level }}>{{ $title }}</h{{ $level }}>
    @if ($text)
        <p>{{ $text }}</p>
    @endif
</div>
