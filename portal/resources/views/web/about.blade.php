{{-- About Samaj (web/about.html). Text: Admin › Page Content + Site Texts. Committee: Admin › Committee. --}}
<x-layouts.web :title="__('web.nav.about')" :description="__('web.about.meta_description')">
    <x-page-hero :title="__('web.about.hero_title')" :subtitle="__('web.about.hero_subtitle')"
                 :breadcrumbs="[__('web.nav.about') => null]" />

    @if ($history)
        <section class="section">
            <div class="container">
                <div class="grid grid-2 align-center">
                    <div>
                        <h2>{{ $history->t('title') }}</h2>
                        @foreach ($history->paragraphs() as $p)<p>{{ $p }}</p>@endforeach
                    </div>
                    <div class="card-media media-rounded">
                        @if ($history->image)<x-img :image="$history->image" alt="" sizes="(max-width: 600px) 100vw, 50vw" />@else<x-media-placeholder />@endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($values->isNotEmpty())
        <section class="section section-alt">
            <div class="container">
                <div @class(['grid', 'grid-3' => $values->count() !== 2 && $values->count() !== 4, 'grid-2' => $values->count() === 2, 'grid-4' => $values->count() === 4])>
                    @foreach ($values as $card)
                        <div class="card"><div class="card-body">
                            <h3>{{ $card->t('title') }}</h3>
                            <p>{{ $card->t('body') }}</p>
                        </div></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section">
        <div class="container">
            <x-section-head :eyebrow="__('web.about.leadership_eyebrow')" :title="__('web.about.leadership_heading')" />
            @if ($leaders->isEmpty())
                <x-empty-state :title="__('web.committee.leadership_empty')" />
            @else
                <div class="grid grid-4">
                    @foreach ($leaders as $leader)
                        @include('web.partials.leader-card', ['m' => $leader])
                    @endforeach
                </div>
            @endif
            @if ($hasGeneral || $leaders->isNotEmpty())
                <div class="section-actions">
                    <a href="{{ localized_route('committee') }}" class="btn btn-outline">{{ __('web.about.leadership_btn') }}</a>
                    <p class="muted section-note">{{ __('web.about.leadership_note') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.web>
