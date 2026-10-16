{{-- Live campaign totals (real database values). --}}
@php $pct = $campaign->progressPercent(); @endphp
<div class="campaign-progress">
    <div class="flex-between flex-wrap gap-3">
        <span class="donation-amount">{{ inr($campaign->raised_amount) }}</span>
        @if ($campaign->target_amount && ($showTarget ?? true))
            <span class="muted progress-target">{{ __('web.donations.of_target', ['target' => inr($campaign->target_amount)]) }}</span>
        @endif
    </div>
    @if ($pct !== null)
        <div class="progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}" aria-label="{{ __('web.donations.percent', ['percent' => $pct]) }}">
            <div class="progress-fill" style="width:0" data-target-width="{{ max($pct, $campaign->raised_amount > 0 ? 2 : 0) }}"></div>
        </div>
    @endif
    <div class="meta-row">
        @if ($showDonors ?? true)<span>{{ trans_choice('web.donations.donors', $campaign->donor_count, ['count' => number_format($campaign->donor_count)]) }}</span>@endif
        @if ($pct !== null && ($showPercent ?? true))<span>{{ __('web.donations.percent', ['percent' => $pct]) }}</span>@endif
    </div>
</div>
