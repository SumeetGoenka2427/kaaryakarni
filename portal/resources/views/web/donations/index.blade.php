{{-- Donations (web/donations.html): permanent Samaj Donation + other campaigns + donate form. --}}
<x-layouts.web :title="__('web.nav.donations')" :description="__('web.donations.meta_description')">
    <x-page-hero :title="__('web.donations.hero_title')" :subtitle="__('web.donations.hero_subtitle')" :breadcrumbs="[__('web.nav.donations') => null]" />

    @if ($permanent)
        <section class="section" id="samaj-donation">
            <div class="container">
                <div class="card permanent-campaign">
                    <div class="card-body permanent-body">
                        <div class="permanent-text">
                            <x-pill variant="neutral" class="pill-start">{{ __('web.donations.permanent_label') }}</x-pill>
                            <h2>{{ $permanent->t('title') }}</h2>
                            @if ($permanent->t('description'))<p>{{ $permanent->t('description') }}</p>@endif
                        </div>
                        <div class="permanent-side">
                            @include('web.donations.partials.progress', ['campaign' => $permanent])
                            <div class="flex gap-3 flex-wrap">
                                <a href="{{ localized_route('donations.show', ['campaign' => $permanent->slug]) }}#donate" class="btn btn-primary flex-1">{{ __('web.donations.donate_now') }}</a>
                                @if ($permanent->sellsTickets())
                                    <a href="{{ localized_route('donations.show', ['campaign' => $permanent->slug, 'purpose' => 'ticket']) }}#donate" class="btn btn-outline flex-1">{{ __('web.donations.buy_ticket') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="section section-alt">
        <div class="container">
            <x-section-head :eyebrow="__('web.donations.others_eyebrow')" :title="__('web.donations.others_heading')" />
            @if ($others->isEmpty())
                <x-empty-state :title="__('web.donations.none_active')"><p>{{ __('web.donations.none_active_text') }}</p></x-empty-state>
            @else
                <div class="grid grid-3">
                    @foreach ($others as $campaign)
                        @include('web.donations.partials.campaign-card')
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($campaigns->isNotEmpty())
        <section class="section">
            <div class="container container-form">
                <x-section-head :eyebrow="__('web.donations.form_eyebrow')" :title="__('web.donations.form_heading')" />
                @include('web.donations.partials.form')
            </div>
        </section>
    @endif
</x-layouts.web>
