{{-- Business card (web/business-directory.html + homepage highlights). --}}
<a href="{{ localized_route('business.show', ['business' => $business->slug]) }}" class="card link-card business-card">
    <div class="card-media">
        <x-img :image="$business->logo" alt="" class="cover-img" sizes="(max-width: 600px) 100vw, 33vw" :fallback="asset('images/sample/'.(($business->id % 6) + 1).'.jpg')" />
    </div>
    <div class="card-body">
        <x-pill :variant="$business->is_featured ? 'success' : 'neutral'">{{ __($business->is_featured ? 'web.business.featured' : 'web.business.approved') }}</x-pill>
        <h3 class="business-name">{{ $business->name }}</h3>
        <p class="muted business-meta">{{ $business->category?->t('name') }} · {{ $business->area?->t('name') }}</p>
    </div>
</a>
