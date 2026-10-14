{{--
    Responsive image from ImageService data: <x-img :image="$slide->image" alt="..." sizes="(max-width: 1024px) 100vw, 56vw" />
    Explicit width/height prevent layout shift; lazy by default, `priority` for the first (LCP) image.
    `fallback` is a static URL used when no image has been uploaded.
--}}
@props(['image' => null, 'alt' => '', 'sizes' => '100vw', 'priority' => false, 'fallback' => null])
@php
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
@endphp
@if ($image && ! empty($image['variants']))
    <img src="{{ $disk->url($image['path']) }}"
         srcset="{{ collect($image['variants'])->map(fn ($p, $w) => $disk->url($p).' '.$w.'w')->implode(', ') }}"
         sizes="{{ $sizes }}" width="{{ $image['width'] }}" height="{{ $image['height'] }}" alt="{{ $alt }}"
         @if ($priority) fetchpriority="high" @else loading="lazy" @endif decoding="async" {{ $attributes }}>
@elseif ($fallback)
    <img src="{{ $fallback }}" alt="{{ $alt }}" @if ($priority) fetchpriority="high" @else loading="lazy" @endif decoding="async" {{ $attributes }}>
@endif
