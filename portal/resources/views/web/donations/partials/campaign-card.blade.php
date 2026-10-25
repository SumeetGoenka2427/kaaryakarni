{{-- Campaign card (web/index.html + web/donations.html "Active Campaigns"). --}}
<article class="card">
    <a href="{{ localized_route('donations.show', ['campaign' => $campaign->slug]) }}" class="card-media media-link" tabindex="-1" aria-hidden="true">
        @if ($campaign->image)<x-img :image="$campaign->image" alt="" sizes="(max-width: 600px) 100vw, 33vw" class="cover-img" />@else<x-media-placeholder :text="$campaign->t('title')" class="cover-img" />@endif
    </a>
    <div class="card-body">
        <x-pill :variant="$campaign->is_permanent ? 'neutral' : 'warning'">{{ __($campaign->is_permanent ? 'web.donations.permanent_label' : 'web.donations.active_label') }}</x-pill>
        <h3><a href="{{ localized_route('donations.show', ['campaign' => $campaign->slug]) }}" class="card-title-link">{{ $campaign->t('title') }}</a></h3>
        @if ($campaign->t('description'))<p>{{ \Illuminate\Support\Str::limit($campaign->t('description'), 160) }}</p>@endif
        @include('web.donations.partials.progress')
        <div class="flex gap-3 card-actions">
            <a href="{{ localized_route('donations.show', ['campaign' => $campaign->slug]) }}#donate" class="btn btn-primary flex-1">{{ __('web.donations.donate_now') }}</a>
            @if ($campaign->sellsTickets())
                <a href="{{ localized_route('donations.show', ['campaign' => $campaign->slug, 'purpose' => 'ticket']) }}#donate" class="btn btn-outline flex-1">{{ __('web.donations.buy_ticket') }}</a>
            @endif
        </div>
    </div>
</article>
