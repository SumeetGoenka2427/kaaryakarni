{{-- Listing missing, pending, rejected or hidden (design: business-detail.html not-found state). --}}
<x-layouts.web :title="__('web.business.not_found')" noindex>
    <section class="section">
        <div class="container">
            <x-empty-state :title="__('web.business.not_found')" icon="search">
                <p>{{ __('web.business.not_found_text') }}</p>
                <a href="{{ localized_route('business.index') }}" class="btn btn-primary">{{ __('web.business.back') }}</a>
            </x-empty-state>
        </div>
    </section>
</x-layouts.web>
