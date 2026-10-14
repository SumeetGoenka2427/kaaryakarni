<?php

namespace App\Http\Controllers\Member;

use App\Enums\OfflineMethod;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Services\Membership\MembershipService;
use App\Services\Payments\PaymentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Member → My Membership + Pay Membership Fee (lifetime, one-time). */
class MembershipController extends Controller
{
    public function __construct(
        private readonly MembershipService $memberships,
        private readonly PaymentService $payments,
    ) {}

    public function show(Request $request): View
    {
        $member = $request->user('member');
        $membership = $this->memberships->forMember($member)->load('payment.receipt');
        $lastRejected = $membership->payments()->where('status', PaymentStatus::Cancelled)->latest('id')->first();

        return view('member.membership', compact('member', 'membership', 'lastRejected'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $member = $request->user('member');
        $membership = $this->memberships->forMember($member);

        if (! $membership->canPay()) {
            return redirect(localized_route('member.membership'))->with('status', __('web.pay.not_payable'));
        }

        return view('member.pay-membership', [
            'member' => $member,
            'membership' => $membership,
            'onlineAvailable' => $this->payments->onlineAvailable(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = $request->user('member');
        $membership = $this->memberships->forMember($member);

        if (! $membership->canPay()) {
            return redirect(localized_route('member.membership'))->with('status', __('web.pay.not_payable'));
        }

        $mode = $request->validate(['mode' => ['required', Rule::in(['online', 'offline'])]])['mode'];

        try {
            if ($mode === 'online') {
                [$payment] = $this->payments->startOnline($membership, PaymentPurpose::Membership, (float) $membership->fee_amount, $member);
                $payment->meta = ['return' => 'member.membership', 'locale' => app()->getLocale()];
                $payment->save();

                return redirect(localized_route('payments.checkout', ['payment' => $payment->ulid]));
            }

            $details = $request->validate([
                'offline_method' => ['required', Rule::enum(OfflineMethod::class)],
                'offline_reference' => ['nullable', 'required_unless:offline_method,cash', 'string', 'max:100'],
                'offline_paid_on' => ['required', 'date', 'before_or_equal:today', 'after:'.now()->subYear()->toDateString()],
                'proof' => PaymentService::proofRules(),
            ], [], [
                'offline_method' => __('web.pay.method'), 'offline_reference' => __('web.pay.reference'),
                'offline_paid_on' => __('web.pay.paid_on'), 'proof' => __('web.pay.proof'),
            ]);

            $this->payments->submitOffline($membership, PaymentPurpose::Membership, (float) $membership->fee_amount, $member, $details, $request->file('proof'));
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect(localized_route('member.membership'))->with('success', __('web.pay.offline_submitted'));
    }
}
