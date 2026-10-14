{{-- Membership (web/membership.html). One-time lifetime membership — no renewal anywhere. --}}
<x-layouts.web :title="__('web.nav.membership')" :description="__('web.membership.meta_description')">
    <x-page-hero :title="__('web.membership.hero_title')" :subtitle="__('web.membership.hero_subtitle')"
                 :breadcrumbs="[__('web.nav.membership') => null]" />

    @if ($benefits->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="grid grid-3">
                    @foreach ($benefits as $i => $card)
                        <div class="card"><div class="card-body">
                            @if ($card->t('label'))<x-pill :variant="$i === 0 ? 'success' : 'neutral'" class="pill-start">{{ $card->t('label') }}</x-pill>@endif
                            <h3>{{ $card->t('title') }}</h3>
                            <p>{{ $card->t('body') }}</p>
                        </div></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section section-alt">
        <div class="container">
            <div class="grid grid-2 align-start">
                <div>
                    <h2>{{ __('web.membership.fee_heading') }}</h2>
                    <div class="card">
                        <div class="card-body">
                            <div class="flex-between flex-wrap gap-3">
                                <span class="muted">{{ __('web.membership.fee_label') }}</span>
                                <span class="donation-amount">{{ inr(setting('membership_fee')) }}</span>
                            </div>
                            <p class="muted card-note">{{ __('web.membership.fee_note') }}</p>
                        </div>
                    </div>
                    @if ($included && $included->lines())
                        <h3 class="included-heading">{{ __('web.membership.included_heading') }}</h3>
                        <ul class="check-list">
                            @foreach ($included->lines() as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div>
                    <h2>{{ __('web.membership.steps_heading') }}</h2>
                    <ol class="stack step-list">
                        @foreach ($steps as $i => $step)
                            <li class="card"><div class="card-body step-card">
                                <span class="pill pill-neutral step-number" aria-hidden="true">{{ $i + 1 }}</span>
                                <div>
                                    <h3>{{ $step->t('title') }}</h3>
                                    @if ($step->t('body'))<p class="muted">{{ $step->t('body') }}</p>@endif
                                </div>
                            </div></li>
                        @endforeach
                    </ol>
                    <a href="{{ localized_route('register') }}" class="btn btn-primary btn-block steps-cta">{{ __('web.membership.start') }}</a>
                </div>
            </div>
        </div>
    </section>

    @if ($faqs->isNotEmpty())
        <section class="section">
            <div class="container container-narrow">
                <x-section-head :eyebrow="__('web.membership.faq_eyebrow')" :title="__('web.membership.faq_heading')" />
                <div>
                    @foreach ($faqs as $i => $faq)
                        <div class="accordion-item">
                            <h3 class="accordion-heading">
                                <button type="button" class="accordion-trigger" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faq-{{ $faq->id }}">
                                    {{ $faq->t('title') }} <span class="icon" aria-hidden="true">+</span>
                                </button>
                            </h3>
                            <div class="accordion-panel" id="faq-{{ $faq->id }}" data-open="{{ $i === 0 ? 'true' : 'false' }}">
                                <div class="accordion-panel-inner">{{ $faq->t('body') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.web>
