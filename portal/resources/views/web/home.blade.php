{{--
    Homepage (from web/index.html). P02 renders the static sections; the
    data-driven sections are added by their parts: top banner + stats (P08),
    campaigns (P07), featured businesses (P09), events + gallery (P11).
--}}
<x-layouts.web>

    <section class="section">
        <div class="container">
            <div class="grid grid-2 align-center">
                <div class="card-media media-rounded">
                    <img src="{{ asset('images/sample/1.jpg') }}" width="678" height="452" alt="" loading="lazy" decoding="async">
                </div>
                <div>
                    <h2>{{ __('web.home.about_heading') }}</h2>
                    <p>{{ __('web.home.about_text') }}</p>
                    @if (Route::has('about'))
                        <a href="{{ localized_route('about') }}" class="btn btn-outline">{{ __('web.home.about_cta') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <div class="card cta-band">
                <div class="card-body">
                    <div>
                        <h2>{{ __('web.home.cta_heading') }}</h2>
                        <p>{{ __('web.home.cta_text') }}</p>
                    </div>
                    @if (Route::has('register'))
                        <a href="{{ localized_route('register') }}" class="btn btn-gold">{{ __('web.home.cta_button') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

</x-layouts.web>
