{{--
    Status pill. Pass an enum with label()/pill() — <x-pill :status="$member->status" />
    or a plain label + variant — <x-pill variant="warning">Upcoming</x-pill>
--}}
@props(['status' => null, 'variant' => 'neutral'])
@php
    $variant = $status && method_exists($status, 'pill') ? $status->pill() : $variant;
@endphp
<span {{ $attributes->class(['pill', 'pill-'.$variant]) }}>{{ $status ? $status->label() : $slot }}</span>
