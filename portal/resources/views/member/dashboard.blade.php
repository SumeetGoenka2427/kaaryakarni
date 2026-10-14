{{-- Member dashboard (from web/member/dashboard.html). Module cards are added as each part goes live. --}}
@php use App\Enums\MemberStatus; @endphp
<x-layouts.member :title="__('web.dashboard.title')">
    <section class="page-hero page-hero-compact">
        <div class="container">
            <h1>{{ __('web.dashboard.welcome', ['name' => $member->firstName()]) }}</h1>
            <p>{{ __('web.dashboard.subtitle') }}</p>
        </div>
    </section>

    <section class="section section-tight">
        <div class="container">
            @if ($member->status === MemberStatus::UnderVerification)
                <div class="form-alert form-alert-info" role="status">{{ __('web.dashboard.under_verification_note') }}</div>
            @elseif ($member->status === MemberStatus::Rejected)
                <div class="form-alert form-alert-danger" role="status">{{ __('web.dashboard.rejected_note', ['reason' => $member->rejection_reason]) }}</div>
            @endif

            <div class="grid grid-3 dashboard-cards">
                <div class="card"><div class="card-body">
                    <span class="panel-label">{{ __('web.dashboard.verification') }}</span>
                    <h2 class="card-value"><x-pill :status="$member->status" /></h2>
                    <p class="muted card-note">{{ __('web.dashboard.managed_by_admin') }}</p>
                </div></div>
                <div class="card"><div class="card-body">
                    <span class="panel-label">{{ __('web.dashboard.area') }}</span>
                    <h2 class="card-value">{{ $member->area?->t('name') ?? __('web.dashboard.no_area') }}</h2>
                    <p class="muted card-note">{{ __('web.common.jaipur') }}</p>
                </div></div>
                <div class="card"><div class="card-body">
                    <span class="panel-label">{{ __('web.dashboard.mobile') }}</span>
                    <h2 class="card-value">{{ $member->mobile }}</h2>
                    <p class="muted card-note">{{ __('web.dashboard.member_since', ['date' => $member->created_at->translatedFormat('d M Y')]) }}</p>
                </div></div>
            </div>
        </div>
    </section>
</x-layouts.member>
