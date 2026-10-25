{{-- Shown when an admin/member has not uploaded an image: branded tile with initials (never a stock or demo photo). --}}
@props(['text' => null])
<div {{ $attributes->class('media-placeholder') }} aria-hidden="true">
    @if ($text)
        <span class="media-placeholder-initials">{{ initials($text, 2) }}</span>
    @else
        <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M21 17l-5-5-4 4-3-3-4 4"/></svg>
    @endif
</div>
