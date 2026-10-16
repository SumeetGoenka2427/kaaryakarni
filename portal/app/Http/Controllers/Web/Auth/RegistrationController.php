<?php

namespace App\Http\Controllers\Web\Auth;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\RegisterMemberRequest;
use App\Models\Area;
use App\Models\Member;
use App\Services\Members\MemberService;
use App\Services\Notifications\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Step 3 of registration: member details after the mobile number is verified.
 * The mobile number is taken from the session (set by a successful OTP),
 * never from the submitted form.
 */
class RegistrationController extends Controller
{
    private const VERIFIED_FOR_MINUTES = 30;

    public function create(Request $request): View|RedirectResponse
    {
        if (! $mobile = $this->verifiedMobile($request)) {
            return redirect(localized_route('register'))->with('warning', __('web.auth.verify_first'));
        }

        return view('web.auth.register-details', [
            'mobile' => $mobile,
            'areas' => Area::activeList()->mapWithKeys(fn ($a) => [$a->id => $a->t('name')]),
        ]);
    }

    public function store(RegisterMemberRequest $request, Notifier $notifier, MemberService $members): RedirectResponse
    {
        if (! $mobile = $this->verifiedMobile($request)) {
            return redirect(localized_route('register'))->with('warning', __('web.auth.verify_first'));
        }

        $member = DB::transaction(function () use ($request, $mobile) {
            if (Member::withTrashed()->where('mobile', $mobile)->lockForUpdate()->exists()) {
                return null;
            }

            $member = new Member($request->safe()->except('consent'));
            $member->mobile = $mobile;
            $member->locale = app()->getLocale();
            $member->status = MemberStatus::UnderVerification;
            $member->consented_at = now();
            $member->profile_submitted_at = now();
            $member->save();

            return $member;
        });

        $request->session()->forget('registration');

        if (! $member) {
            return redirect(localized_route('login'))->with('warning', __('web.auth.already_registered'));
        }

        $members->recordSubmitted($member);
        Auth::guard('member')->login($member);
        $request->session()->regenerate();

        $notifier->member($member, 'registration_received', ['name' => $member->firstName()]);

        return redirect(localized_route('member.dashboard'))->with('success', __('web.auth.registered'));
    }

    private function verifiedMobile(Request $request): ?string
    {
        $data = $request->session()->get('registration');

        if (! $data || now()->timestamp - $data['verified_at'] > self::VERIFIED_FOR_MINUTES * 60) {
            return null;
        }

        return $data['mobile'];
    }
}
