<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Otp\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Member login and registration share one mobile → OTP flow.
 *
 * After a correct OTP: an existing member is logged in; an unknown number
 * continues to the registration details form. The response never reveals
 * whether a mobile number is registered.
 */
class OtpController extends Controller
{
    public const MOBILE_RULE = 'regex:/^[6-9]\d{9}$/';

    public function __construct(private readonly OtpService $otp) {}

    public function showLogin(): View
    {
        return view('web.auth.mobile', ['purpose' => 'login']);
    }

    public function showRegister(): View
    {
        return view('web.auth.mobile', ['purpose' => 'register']);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'string', self::MOBILE_RULE],
            'purpose' => ['required', 'in:login,register'],
        ], ['mobile.regex' => __('web.auth.mobile_invalid')]);

        $result = $this->otp->send($data['mobile'], $data['purpose'], $request->ip(), app()->getLocale());

        if (! $result['ok'] && $result['error'] !== 'cooldown') {
            return back()->withInput()->withErrors(['mobile' => $this->errorMessage($result)]);
        }

        $request->session()->put('otp', ['mobile' => $data['mobile'], 'purpose' => $data['purpose']]);

        return redirect(localized_route('otp.verify'))
            ->with($result['ok'] ? 'status' : 'warning', $result['ok'] ? __('web.auth.otp_sent') : $this->errorMessage($result));
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        $otp = $request->session()->get('otp');
        if (! $otp) {
            return redirect(localized_route('login'));
        }

        return view('web.auth.verify', [
            'purpose' => $otp['purpose'],
            'maskedMobile' => substr($otp['mobile'], 0, 2).'XXXXXX'.substr($otp['mobile'], -2),
            'resendIn' => $this->otp->secondsUntilResend($otp['mobile'], $otp['purpose']),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $otp = $request->session()->get('otp');
        if (! $otp) {
            return redirect(localized_route('login'));
        }

        $request->validate(['otp' => ['required', 'digits:'.config('portal.otp.length')]], [
            'otp.required' => __('web.auth.otp_incomplete'),
            'otp.digits' => __('web.auth.otp_incomplete'),
        ]);

        $result = $this->otp->verify($otp['mobile'], $otp['purpose'], $request->input('otp'));

        if (! $result['ok']) {
            return back()->withErrors(['otp' => $this->errorMessage($result)]);
        }

        $request->session()->forget('otp');
        $member = Member::withTrashed()->where('mobile', $otp['mobile'])->first();

        if ($member?->trashed()) {
            return redirect(localized_route('login'))->withErrors(['mobile' => __('web.auth.account_removed')]);
        }

        if (! $member) {
            // New number: continue registration. The verified mobile lives only in the session.
            $request->session()->put('registration', ['mobile' => $otp['mobile'], 'verified_at' => now()->timestamp]);

            return redirect(localized_route('register.details'));
        }

        if ($member->isSuspended()) {
            return redirect(localized_route('login'))->withErrors(['mobile' => __('web.auth.account_suspended')]);
        }

        Auth::guard('member')->login($member);
        $request->session()->regenerate();
        $member->forceFill(['last_login_at' => now(), 'locale' => app()->getLocale()])->save();

        return redirect()->intended(localized_route('member.dashboard'))
            ->with('success', __('web.auth.welcome_back', ['name' => $member->firstName()]));
    }

    public function resend(Request $request): RedirectResponse
    {
        $otp = $request->session()->get('otp');
        if (! $otp) {
            return redirect(localized_route('login'));
        }

        $result = $this->otp->send($otp['mobile'], $otp['purpose'], $request->ip(), app()->getLocale());

        return back()->with($result['ok'] ? 'status' : 'warning', $result['ok'] ? __('web.auth.otp_resent') : $this->errorMessage($result));
    }

    /** Use a different mobile number. */
    public function restart(Request $request): RedirectResponse
    {
        $purpose = $request->session()->pull('otp')['purpose'] ?? 'login';

        return redirect(localized_route($purpose === 'register' ? 'register' : 'login'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(localized_route('home'))->with('status', __('web.auth.logged_out'));
    }

    private function errorMessage(array $result): string
    {
        $minutes = max(1, (int) ceil(($result['retry_after'] ?? 60) / 60));

        return match ($result['error']) {
            'cooldown' => __('web.auth.otp_cooldown', ['seconds' => $result['retry_after']]),
            'mobile_limit', 'ip_limit' => __('web.auth.otp_rate_limited', ['minutes' => $minutes]),
            'sms_failed' => __('web.auth.otp_send_failed'),
            'expired' => __('web.auth.otp_expired'),
            'too_many_attempts' => __('web.auth.otp_too_many'),
            'invalid' => __('web.auth.otp_invalid', ['left' => $result['attempts_left']]),
            default => __('web.errors.500_text'),
        };
    }
}
