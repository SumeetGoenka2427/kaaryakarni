{{-- Committee Members (web/committee-members.html). Data: Admin › Committee. --}}
<x-layouts.web :title="__('web.committee.title')" :description="__('web.committee.meta_description')">
    <x-page-hero :title="__('web.committee.hero_title')" :subtitle="__('web.committee.hero_subtitle')"
                 :breadcrumbs="[__('web.nav.about') => localized_route('about'), __('web.committee.title') => null]" />

    <section class="section">
        <div class="container">
            <x-section-head :eyebrow="__('web.committee.leadership_eyebrow')" :title="__('web.committee.leadership_heading')" />
            @if ($leaders->isEmpty())
                <x-empty-state :title="__('web.committee.leadership_empty')" />
            @else
                <div class="grid grid-4">
                    @foreach ($leaders as $leader)
                        @include('web.partials.leader-card', ['m' => $leader])
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section section-alt" id="general">
        <div class="container">
            <x-section-head :eyebrow="__('web.committee.general_eyebrow')" :title="__('web.committee.general_heading')" />
            @if ($general->isEmpty())
                <x-empty-state :title="__('web.committee.general_empty')" />
            @else
                <ul class="member-chip-grid" role="list">
                    @foreach ($general as $m)
                        <li class="member-chip">
                            <span class="initials" aria-hidden="true">{{ $m->initials() }}</span>
                            <span class="member-info"><strong>{{ $m->t('name') }}</strong><span>{{ $m->t('designation') }}</span></span>
                        </li>
                    @endforeach
                </ul>
                <div class="section-actions">
                    <p class="muted">{{ __('web.committee.showing', ['shown' => $general->count(), 'total' => $generalTotal]) }}</p>
                    @if ($general->count() < $generalTotal)
                        <a href="{{ localized_route('committee', ['show' => $show + 20]) }}#general" class="btn btn-outline">{{ __('web.committee.load_more') }}</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
</x-layouts.web>
