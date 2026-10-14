{{-- My Membership (web/member/membership.html). Lifetime: no renewal, no expiry anywhere. --}}
@php use App\Enums\MembershipStatus as MS; $st = $membership->status; @endphp
<x-layouts.member :title="__('web.my_membership.title')">
    <section class="section section-tight">
        <div class="container container-form">
            <h1>{{ __('web.my_membership.title') }}</h1>
            <p class="muted">{{ __('web.my_membership.intro') }}</p>

            @if ($st === MS::PaymentPending)
                @if ($lastRejected)
                    <div class="form-alert form-alert-danger" role="status">{{ __('web.my_membership.rejected_payment', ['reason' => $lastRejected->failure_reason]) }}</div>
                @endif
                <div class="form-alert form-alert-warning alert-with-action" role="status">
                    <span>{{ __('web.my_membership.pay_cta') }}</span>
                    <a href="{{ localized_route('member.membership.pay') }}" class="btn btn-primary btn-sm">{{ __('web.my_membership.pay_now') }}</a>
                </div>
            @elseif ($st === MS::PaymentSubmitted)
                <div class="form-alert form-alert-info" role="status">{{ __('web.my_membership.submitted_note') }}</div>
            @elseif ($st === MS::UnderVerification)
                <div class="form-alert form-alert-info" role="status">{{ __('web.my_membership.verification_note') }}</div>
            @elseif ($st === MS::Active)
                <div class="form-alert form-alert-success" role="status">{{ __('web.my_membership.active_note') }}</div>
            @endif

            <div class="card">
                <div class="card-body detail-rows">
                    <div class="flex-between"><span class="muted">{{ __('web.my_membership.membership_id') }}</span><strong>{{ $member->membership_no ?? __('web.my_membership.not_set') }}</strong></div>
                    <div class="flex-between"><span class="muted">{{ __('web.my_membership.member_name') }}</span><strong>{{ $member->name }}</strong></div>
                    <div class="flex-between"><span class="muted">{{ __('web.my_membership.status') }}</span><x-pill :status="$st" /></div>
                    <div class="flex-between"><span class="muted">{{ __('web.my_membership.type') }}</span><strong>{{ __('web.my_membership.type_value') }}</strong></div>
                    <div class="flex-between"><span class="muted">{{ __('web.my_membership.fee') }}</span><strong>{{ inr($membership->fee_amount) }}</strong></div>
                    @if ($membership->activated_at)
                        <div class="flex-between"><span class="muted">{{ __('web.my_membership.member_since') }}</span><strong>{{ $membership->activated_at->translatedFormat('d M Y') }}</strong></div>
                    @endif
                </div>
            </div>

            @if ($membership->payment?->receipt)
                <a href="{{ localized_route('member.payments.receipt', ['payment' => $membership->payment->ulid]) }}" class="btn btn-outline btn-block steps-cta">{{ __('web.my_membership.download_receipt') }}</a>
            @endif
        </div>
    </section>
</x-layouts.member>
