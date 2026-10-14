{{-- Left / right homepage banner panel. Mode is chosen in Admin › Homepage Banners. --}}
@php
    $mode = $panel?->mode ?? ($position === 'left' ? 'membership_cta' : 'advertisement');
    // Campaign modes fall back to the permanent Samaj Donation if no / a closed campaign is selected.
    $campaign = null;
    if (str_starts_with($mode, 'campaign_')) {
        $open = app(\App\Services\Content\PublicContent::class)->campaigns();
        $campaign = $open->firstWhere('id', $panel?->campaign_id) ?? $open->firstWhere('is_permanent', true) ?? $open->first();
        if (! $campaign) {
            $mode = 'membership_cta';
        }
    }
@endphp

@if ($mode === 'campaign_progress')
    {{-- Full campaign panel (design: left "Active Campaign" panel) --}}
    <aside class="donation-panel" aria-label="{{ __('web.donations.share_progress') }}">
        <span class="panel-label">{{ __($campaign->is_permanent ? 'web.donations.permanent_label' : 'web.donations.active_label') }}</span>
        <h3>{{ $campaign->t('title') }}</h3>
        @if ($campaign->t('description'))<p class="muted panel-note">{{ \Illuminate\Support\Str::limit($campaign->t('description'), 140) }}</p>@endif
        @include('web.donations.partials.progress', ['showTarget' => $panel->option('show_target'), 'showPercent' => $panel->option('show_percent'), 'showDonors' => $panel->option('show_donors')])
        @if ($panel->option('show_recent'))
            @php $recent = app(\App\Services\Content\PublicContent::class)->recentDonations($campaign->id, 2); @endphp
            @if ($recent->isNotEmpty())
                <div class="panel-recent">
                    @foreach ($recent as $d)<div class="flex-between"><span>{{ $d->publicName() }}</span><strong>{{ inr($d->amount) }}</strong></div>@endforeach
                </div>
            @endif
        @endif
        <a href="{{ localized_route('donations.show', ['campaign' => $campaign->slug]) }}#donate" class="btn btn-primary btn-sm btn-block">{{ __('web.donations.donate_now') }}</a>
    </aside>
@elseif ($mode === 'campaign_compact')
    {{-- Compact campaign panel: different layout so left/right never look duplicated --}}
    @php $pct = $campaign->progressPercent(); @endphp
    <aside class="donation-panel panel-compact" aria-label="{{ __('web.donations.share_progress') }}">
        <span class="panel-label">{{ $campaign->t('title') }}</span>
        <div class="compact-figure">
            <span class="muted compact-caption">{{ __('web.donations.collected') }}</span>
            <span class="donation-amount">{{ inr_short($campaign->raised_amount) }}</span>
            @if ($campaign->target_amount && $panel->option('show_target'))<span class="muted compact-caption">{{ __('web.donations.of_target', ['target' => inr_short($campaign->target_amount)]) }}</span>@endif
        </div>
        @if ($pct !== null)
            <div class="progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}">
                <div class="progress-fill" style="width:0" data-target-width="{{ max($pct, $campaign->raised_amount > 0 ? 2 : 0) }}"></div>
            </div>
        @endif
        <div class="compact-stats">
            @if ($pct !== null && $panel->option('show_percent'))<div><strong>{{ $pct }}%</strong><span>{{ __('web.donations.target') }}</span></div>@endif
            @if ($panel->option('show_donors'))<div><strong>{{ number_format($campaign->donor_count) }}</strong><span>{{ __('web.donations.donors_label') }}</span></div>@endif
        </div>
        <a href="{{ localized_route('donations.show', ['campaign' => $campaign->slug]) }}#donate" class="btn btn-gold btn-sm btn-block">{{ __('web.donations.donate_now') }}</a>
    </aside>
@elseif ($mode === 'advertisement' && $panel?->ad_image)
    @php $adUrl = $panel->safeAdUrl(); @endphp
    <aside class="donation-panel ad-panel" aria-label="{{ __('web.banner.ad_label') }}">
        <span class="panel-label">{{ __('web.banner.ad_label') }}</span>
        <{{ $adUrl ? 'a' : 'div' }} @if ($adUrl) href="{{ $adUrl }}" @if (str_starts_with($adUrl, 'https://')) target="_blank" rel="noopener sponsored" @endif @endif class="ad-creative">
            <x-img :image="$panel->ad_image" :alt="$panel->t('ad_label') ?? __('web.banner.ad_label')" sizes="(max-width: 1024px) 100vw, 22vw" />
            <span class="ad-tag">{{ __('web.banner.ad_tag') }}</span>
        </{{ $adUrl ? 'a' : 'div' }}>
        <p class="muted panel-note">{{ __('web.banner.ad_contact') }} <a href="{{ localized_route('contact') }}">{{ __('web.banner.ad_contact_link') }}</a>.</p>
    </aside>
@elseif ($mode === 'advertisement')
    {{-- Advertisement slot without a creative yet: invite advertisers instead of an empty box. --}}
    <aside class="donation-panel ad-panel" aria-label="{{ __('web.banner.ad_label') }}">
        <span class="panel-label">{{ __('web.banner.ad_label') }}</span>
        <h3>{{ $panel?->t('ad_label') ?: __('web.banner.ad_contact') }}</h3>
        <p class="muted panel-note">{{ setting_t('org_name') }}</p>
        <a href="{{ localized_route('contact') }}" class="btn btn-outline btn-sm btn-block">{{ __('web.banner.ad_contact_link') }}</a>
    </aside>
@else
    <aside class="donation-panel" aria-label="{{ __('web.banner.membership_label') }}">
        <span class="panel-label">{{ __('web.banner.membership_label') }}</span>
        <h3>{{ __('web.banner.membership_title') }}</h3>
        <p class="muted panel-note">{{ __('web.banner.membership_text') }}</p>
        <div class="meta-row"><span>{{ __('web.banner.membership_fee') }}</span><strong class="panel-amount">{{ inr(setting('membership_fee')) }}</strong></div>
        <a href="{{ localized_route('register') }}" class="btn btn-primary btn-sm btn-block">{{ __('web.banner.membership_cta') }}</a>
    </aside>
@endif
