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

            {{-- Quick actions (design: action cards). A card appears once its module is live. --}}
            @php
                $actions = collect([
                    ['route' => 'member.profile', 'key' => 'profile', 'icon' => '&#9998;'],
                    ['route' => 'member.family.index', 'key' => 'family', 'icon' => '&#128106;', 'note' => __('web.family.summary', ['count' => $familyCount])],
                    ['route' => 'member.membership', 'key' => 'membership', 'icon' => '&#127894;'],
                    ['route' => 'member.payments', 'key' => 'payments', 'icon' => '&#8377;'],
                    ['route' => 'donations.index', 'key' => 'donate', 'icon' => '&#127873;'],
                    ['route' => 'member.business', 'key' => 'business', 'icon' => '&#127970;'],
                    ['route' => 'member.matrimonial.index', 'key' => 'matrimonial', 'icon' => '&#128150;'],
                ])->filter(fn ($a) => Route::has($a['route']));
            @endphp
            <h2 class="dashboard-subhead">{{ __('web.dashboard.quick_actions') }}</h2>
            <div class="grid grid-4 dashboard-cards">
                @foreach ($actions as $a)
                    @php [$title, $text] = __('web.dashboard.actions.'.$a['key']); @endphp
                    <a href="{{ localized_route($a['route']) }}" class="card action-card">
                        <div class="card-body">
                            <span class="action-icon" aria-hidden="true">{!! $a['icon'] !!}</span>
                            <h3 class="action-title">{{ $title }}</h3>
                            <p class="muted action-text">{{ $a['note'] ?? $text }}</p>
                            <span class="action-link">{{ __('web.dashboard.open') }} <span class="arrow" aria-hidden="true">&rarr;</span></span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.member>
