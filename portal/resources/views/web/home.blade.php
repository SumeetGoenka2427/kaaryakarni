{{--
    Homepage (web/index.html). Every section is admin-managed:
    banner → Admin › Homepage Banners · about/links → Admin › Page Content ·
    campaigns → Admin › Donations (live totals) · events → Admin › Announcements ·
    stats → live database counts · headings → Admin › Site Texts.
    businesses → approved listings (featured first) · albums → Admin › Gallery.
--}}
<x-layouts.web>
    @if ($slides->isNotEmpty())
        @push('preload')
            @php $first = $slides->first()->image; @endphp
            @if ($first)
                <link rel="preload" as="image" imagesrcset="{{ collect($first['variants'])->map(fn ($p, $w) => Storage::disk('public')->url($p).' '.$w.'w')->implode(', ') }}" imagesizes="(max-width: 1024px) 100vw, 56vw">
            @endif
        @endpush
    @endif

    {{-- ===== Top 3-column banner: left panel / center carousel / right panel ===== --}}
    <section class="top-banner" aria-label="{{ __('web.banner.region') }}">
        <div class="container">
            <div class="banner-grid">
                @include('web.partials.banner-panel', ['panel' => $panels->get('left'), 'position' => 'left'])

                <div class="banner-carousel" data-carousel data-autoplay="5500" data-dot-label="{{ __('web.carousel.slide') }}"
                     aria-roledescription="carousel" aria-label="{{ __('web.carousel.label') }}">
                    <div class="carousel-track">
                        @forelse ($slides as $i => $slide)
                            @php $url = $slide->safeCtaUrl(); $tag = $url ? 'a' : 'div'; @endphp
                            <{{ $tag }} @if ($url) href="{{ $url }}" @endif
                                class="carousel-slide @if ($i === 0) is-active @endif"
                                aria-roledescription="slide" aria-label="{{ ($i + 1).' / '.$slides->count() }}">
                                <x-img :image="$slide->image" class="carousel-slide-img" :priority="$i === 0" sizes="(max-width: 1024px) 100vw, 56vw" />
                                <span class="carousel-slide-scrim" aria-hidden="true"></span>
                                <span class="carousel-slide-content">
                                    <span class="carousel-slide-title">{{ $slide->t('title') }}</span>
                                    @if ($slide->t('subtitle'))<span class="carousel-slide-subtitle">{{ $slide->t('subtitle') }}</span>@endif
                                    @if ($url && $slide->t('cta_label'))<span class="carousel-slide-cta">{{ $slide->t('cta_label') }}</span>@endif
                                </span>
                            </{{ $tag }}>
                        @empty
                            <a href="{{ localized_route('register') }}" class="carousel-slide is-active">
                                <span class="carousel-slide-scrim" aria-hidden="true"></span>
                                <span class="carousel-slide-content">
                                    <span class="carousel-slide-title">{{ __('web.banner.membership_title') }}</span>
                                    <span class="carousel-slide-subtitle">{{ __('web.banner.membership_text') }}</span>
                                    <span class="carousel-slide-cta">{{ __('web.banner.membership_cta') }}</span>
                                </span>
                            </a>
                        @endforelse
                    </div>
                    @if ($slides->count() > 1)
                        <div class="carousel-controls">
                            <button type="button" class="carousel-arrow" data-carousel-prev aria-label="{{ __('web.carousel.prev') }}">&#8249;</button>
                            <button type="button" class="carousel-arrow" data-carousel-next aria-label="{{ __('web.carousel.next') }}">&#8250;</button>
                        </div>
                        <div class="carousel-dots" data-carousel-dots role="group" aria-label="{{ __('web.carousel.dots') }}"></div>
                    @endif
                </div>

                @include('web.partials.banner-panel', ['panel' => $panels->get('right'), 'position' => 'right'])
            </div>
        </div>
    </section>

    {{-- ===== About Samaj brief ===== --}}
    @if ($about)
        <section class="section">
            <div class="container">
                <div class="grid grid-2 align-center">
                    <div class="card-media media-rounded">
                        <x-img :image="$about->image" alt="" sizes="(max-width: 600px) 100vw, 50vw" :fallback="asset('images/sample/1.jpg')" />
                    </div>
                    <div>
                        <h2>{{ $about->t('title') }}</h2>
                        @foreach ($about->paragraphs() as $p)<p>{{ $p }}</p>@endforeach
                        @if ($about->safeLinkUrl() && $about->t('label'))
                            <a href="{{ $about->safeLinkUrl() }}" class="btn btn-outline">{{ $about->t('label') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ===== Key statistics (live counts) ===== --}}
    @if ($stats)
    <section class="section section-alt">
        <div class="container">
            <x-section-head :eyebrow="__('web.home.stats_eyebrow')" :title="__('web.home.stats_heading')" />
            <div @class(['grid', 'grid-4' => count($stats) >= 4, 'grid-3' => count($stats) === 3, 'grid-2' => count($stats) === 2, 'grid-1-center' => count($stats) === 1])>
                @foreach ($stats as $stat)
                    <div class="card stat-card">
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="stat-label">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== Membership CTA ===== --}}
    <section class="section">
        <div class="container">
            <div class="card cta-band">
                <div class="card-body">
                    <div>
                        <h2>{{ __('web.home.cta_heading') }}</h2>
                        <p>{{ __('web.home.cta_text') }}</p>
                    </div>
                    <a href="{{ localized_route('register') }}" class="btn btn-gold">{{ __('web.home.cta_button') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Donation campaigns (live totals) ===== --}}
    @if ($campaigns->isNotEmpty())
        <section class="section section-alt" id="donations">
            <div class="container">
                <x-section-head :eyebrow="__('web.home.campaigns_eyebrow')" :title="__('web.home.campaigns_heading')" :text="__('web.home.campaigns_text')" />
                <div class="grid grid-3">
                    @foreach ($campaigns as $campaign)
                        @include('web.donations.partials.campaign-card')
                    @endforeach
                </div>
                <div class="section-actions"><a href="{{ localized_route('donations.index') }}" class="btn btn-outline">{{ __('web.home.campaigns_all') }}</a></div>
            </div>
        </section>
    @endif

    {{-- ===== Business highlights (approved only, featured first) ===== --}}
    @if ($businesses->isNotEmpty())
        <section class="section">
            <div class="container">
                <x-section-head :eyebrow="__('web.home.business_eyebrow')" :title="__('web.home.business_heading')" />
                <div class="grid grid-4">
                    @foreach ($businesses as $business)
                        @include('web.business.partials.card')
                    @endforeach
                </div>
                <div class="section-actions"><a href="{{ localized_route('business.index') }}" class="btn btn-outline">{{ __('web.home.business_all') }}</a></div>
            </div>
        </section>
    @endif

    {{-- ===== Events & announcements ===== --}}
    @if ($announcements->isNotEmpty())
        <section class="section section-alt">
            <div class="container">
                <x-section-head :eyebrow="__('web.home.events_eyebrow')" :title="__('web.home.events_heading')" />
                <div class="grid grid-3">
                    @foreach ($announcements as $item)
                        <article class="card">
                            <div class="card-body">
                                <x-pill :variant="$item->type === 'event' ? 'warning' : 'neutral'">{{ __('web.home.'.($item->type === 'event' ? 'event_upcoming' : 'event_notice')) }}</x-pill>
                                <h3>{{ $item->t('title') }}</h3>
                                @if ($item->event_date)
                                    <time class="muted event-date" datetime="{{ $item->event_date->toDateString() }}">{{ $item->event_date->translatedFormat('d M Y') }}</time>
                                @endif
                                @if ($item->t('body'))<p>{{ $item->t('body') }}</p>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ===== Gallery preview: cover photo of the latest albums ===== --}}
    @if ($albums->isNotEmpty())
        <section class="section">
            <div class="container">
                <x-section-head :eyebrow="__('web.home.gallery_eyebrow')" :title="__('web.home.gallery_heading')" />
                <div class="gallery-grid">
                    @foreach ($albums as $album)
                        <a href="{{ localized_route('gallery.show', ['album' => $album->slug]) }}" class="gallery-tile album-tile">
                            <x-img :image="$album->cover?->image" alt="" class="gallery-img" sizes="(max-width: 640px) 50vw, (max-width: 960px) 33vw, 25vw" />
                            <figcaption>{{ $album->t('title') }}</figcaption>
                        </a>
                    @endforeach
                </div>
                <div class="section-actions"><a href="{{ localized_route('gallery.index') }}" class="btn btn-outline">{{ __('web.home.gallery_all') }}</a></div>
            </div>
        </section>
    @endif

    {{-- ===== Important links ===== --}}
    @if ($links->isNotEmpty())
        <section class="section section-alt">
            <div class="container">
                <x-section-head :eyebrow="__('web.home.links_eyebrow')" :title="__('web.home.links_heading')" />
                <div class="grid grid-4">
                    @foreach ($links as $link)
                        @php $href = $link->safeLinkUrl(); @endphp
                        <{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif class="card link-card">
                            <div class="card-body">
                                <h3>{{ $link->t('title') }}</h3>
                                @if ($link->t('body'))<p class="muted link-card-text">{{ $link->t('body') }}</p>@endif
                            </div>
                        </{{ $href ? 'a' : 'div' }}>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.web>
