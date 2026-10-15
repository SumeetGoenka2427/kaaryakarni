{{-- Business Directory (web/business-directory.html). Approved listings only; server-side search + filters. --}}
<x-layouts.web :title="__('web.business.title')" :description="__('web.business.meta_description')">
    <x-page-hero :title="__('web.business.title')" :subtitle="__('web.business.hero_subtitle')" :breadcrumbs="[__('web.business.title') => null]" />

    <section class="section">
        <div class="container">
            <form method="GET" action="{{ localized_route('business.index') }}" class="filters-bar" role="search">
                <div class="field search-field">
                    <label for="biz-search">{{ __('web.business.search') }}</label>
                    <input type="search" id="biz-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('web.business.search_placeholder') }}" maxlength="100">
                </div>
                <div class="field">
                    <label for="biz-category">{{ __('web.business.category') }}</label>
                    <select id="biz-category" name="category" data-autosubmit>
                        <option value="">{{ __('web.business.all_categories') }}</option>
                        @foreach ($categories as $c)<option value="{{ $c->slug }}" @selected($filters['category'] === $c->slug)>{{ $c->t('name') }}</option>@endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="biz-area">{{ __('web.business.area') }}</label>
                    <select id="biz-area" name="area" data-autosubmit>
                        <option value="">{{ __('web.business.all_areas') }}</option>
                        @foreach ($areas as $a)<option value="{{ $a->id }}" @selected($filters['area'] === $a->id)>{{ $a->t('name') }}</option>@endforeach
                    </select>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('web.business.filter') }}</button>
                    @if (array_filter($filters))<a href="{{ localized_route('business.index') }}" class="btn btn-outline btn-sm">{{ __('web.business.clear') }}</a>@endif
                    <a href="{{ auth('member')->check() ? localized_route('member.business') : localized_route('login') }}" class="btn btn-outline btn-sm">{{ __('web.business.list_yours') }}</a>
                </div>
            </form>

            @if ($businesses->isEmpty())
                <x-empty-state :title="__('web.business.empty')" icon="search"><p>{{ __('web.business.empty_text') }}</p></x-empty-state>
            @else
                <p class="muted results-count" role="status">{{ __('web.business.results', ['count' => $businesses->total()]) }}</p>
                <div class="grid grid-3">
                    @foreach ($businesses as $business)
                        @include('web.business.partials.card')
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $businesses->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.web>
