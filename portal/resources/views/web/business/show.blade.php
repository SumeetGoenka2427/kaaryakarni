{{-- Business detail (web/business-detail.html). Contact details shown only as the owner allowed. --}}
@php
    $mapQuery = $business->mapQuery();
    $website = $business->safeWebsite();
    $jsonLd = array_filter([
        '@context' => 'https://schema.org', '@type' => 'LocalBusiness', 'name' => $business->name,
        'description' => \Illuminate\Support\Str::limit($business->description, 300),
        'telephone' => $business->show_phone && $business->phone ? '+91'.$business->phone : null,
        'url' => $website,
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => $business->address, 'addressLocality' => 'Jaipur', 'addressRegion' => 'Rajasthan', 'addressCountry' => 'IN'],
    ]);
@endphp
<x-layouts.web :title="$business->name" :description="\Illuminate\Support\Str::limit($business->description, 155)"
               :image="$business->logo ? url(Storage::disk('public')->url($business->logo['path'])) : null">
    <x-slot:head>
        <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    </x-slot:head>

    <section class="section section-tight">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ localized_route('home') }}">{{ __('web.nav.home') }}</a> / <a href="{{ localized_route('business.index') }}">{{ __('web.business.title') }}</a> / <span aria-current="page">{{ $business->name }}</span>
            </nav>
            <div class="grid grid-2 align-start">
                <div class="card-media media-rounded business-photo">
                    @if ($business->logo)<x-img :image="$business->logo" alt="{{ $business->name }}" class="cover-img" sizes="(max-width: 600px) 100vw, 50vw" :priority="true" />@else<x-media-placeholder :text="$business->name" class="cover-img" />@endif
                </div>
                <div>
                    <x-pill :variant="$business->is_featured ? 'success' : 'neutral'" class="pill-start">{{ __($business->is_featured ? 'web.business.featured' : 'web.business.approved') }}</x-pill>
                    <h1 class="business-title">{{ $business->name }}</h1>
                    <p class="muted">{{ $business->category?->t('name') }}</p>
                    <dl class="business-facts">
                        <div><dt>{{ __('web.business.category') }}</dt><dd>{{ $business->category?->t('name') }}</dd></div>
                        <div><dt>{{ __('web.business.area') }}</dt><dd>{{ $business->area?->t('name') }}, {{ __('web.common.jaipur') }}</dd></div>
                        @if ($business->address)<div><dt>{{ __('web.business.address') }}</dt><dd>{{ $business->address }}</dd></div>@endif
                        @if ($business->hours)<div><dt>{{ __('web.business.hours') }}</dt><dd>{{ $business->hours }}</dd></div>@endif
                        @if ($business->member)<div><dt>{{ __('web.business.owner') }}</dt><dd>{{ $business->member->name }}</dd></div>@endif
                        @if ($business->show_email && $business->email)<div><dt>{{ __('web.business.email') }}</dt><dd><a href="mailto:{{ $business->email }}">{{ $business->email }}</a></dd></div>@endif
                    </dl>
                    <div class="flex gap-3 flex-wrap">
                        @if ($business->show_phone && $business->phone)<a href="tel:+91{{ $business->phone }}" class="btn btn-primary">{{ __('web.business.call') }}</a>@endif
                        @if ($business->show_whatsapp && $business->whatsapp)<a href="https://wa.me/91{{ $business->whatsapp }}" target="_blank" rel="noopener" class="btn btn-outline">{{ __('web.business.whatsapp') }}</a>@endif
                        @if ($website)<a href="{{ $website }}" target="_blank" rel="noopener nofollow" class="btn btn-outline">{{ __('web.business.website') }}</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container container-narrow">
            <h2>{{ __('web.business.about') }}</h2>
            @foreach (preg_split('/\R{2,}/', $business->description) as $p)<p>{{ $p }}</p>@endforeach
        </div>
    </section>

    @if ($mapQuery)
        <section class="section">
            <div class="container container-narrow">
                <h2>{{ __('web.business.location') }}</h2>
                <p class="muted">{{ $business->address }}</p>
                <div class="map-embed">
                    <iframe src="https://www.google.com/maps?q={{ urlencode($mapQuery) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('web.business.map_title') }}"></iframe>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($mapQuery) }}" target="_blank" rel="noopener" class="btn btn-outline steps-cta">{{ __('web.business.directions') }}</a>
            </div>
        </section>
    @endif

    @if ($related->isNotEmpty())
        <section class="section section-alt">
            <div class="container">
                <x-section-head :eyebrow="__('web.business.more_eyebrow')" :title="__('web.business.more_heading')" />
                <div class="grid grid-3">
                    @foreach ($related as $business)
                        @include('web.business.partials.card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.web>
