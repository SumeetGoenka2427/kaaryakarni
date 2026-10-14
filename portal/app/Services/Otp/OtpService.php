<?php

namespace App\Services\Otp;

use App\Models\OtpCode;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Mobile OTPs for member login / registration (CLAUDE.md §7).
 *
 * - Codes come from random_int and only their hash is stored.
 * - Expire after OTP_TTL_MINUTES, single use, OTP_MAX_ATTEMPTS wrong tries.
 * - Resend cooldown per mobile, hourly caps per mobile and per IP.
 * - Requesting a new code invalidates any earlier unused code.
 * - The code is never logged (except by the local-only `log` SMS driver).
 */
class OtpService
{
    public function __construct(private readonly SmsService $sms) {}

    /**
     * @return array{ok: bool, error?: string, retry_after?: int}
     *                                                            error: cooldown | mobile_limit | ip_limit | sms_failed
     */
    public function send(string $mobile, string $purpose, string $ip, string $locale = 'en'): array
    {
        $config = config('portal.otp');

        if (($wait = $this->secondsUntilResend($mobile, $purpose)) > 0) {
            return ['ok' => false, 'error' => 'cooldown', 'retry_after' => $wait];
        }

        $mobileKey = "otp-mobile:{$mobile}";
        $ipKey = "otp-ip:{$ip}";

        if (RateLimiter::tooManyAttempts($mobileKey, $config['max_per_mobile_per_hour'])) {
            return ['ok' => false, 'error' => 'mobile_limit', 'retry_after' => RateLimiter::availableIn($mobileKey)];
        }
        if (RateLimiter::tooManyAttempts($ipKey, $config['max_per_ip_per_hour'])) {
            return ['ok' => false, 'error' => 'ip_limit', 'retry_after' => RateLimiter::availableIn($ipKey)];
        }

        RateLimiter::hit($mobileKey, 3600);
        RateLimiter::hit($ipKey, 3600);

        // Only the newest code is ever valid.
        OtpCode::where('mobile', $mobile)->where('purpose', $purpose)->whereNull('consumed_at')
            ->where('expires_at', '>', now())->update(['expires_at' => now()]);

        $code = str_pad((string) random_int(0, 10 ** $config['length'] - 1), $config['length'], '0', STR_PAD_LEFT);

        $otp = OtpCode::create([
            'mobile' => $mobile,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes($config['ttl_minutes']),
            'ip' => $ip,
        ]);

        $sent = $this->sms->sendNow($mobile, 'otp', ['otp' => $code, 'minutes' => $config['ttl_minutes']], $locale);

        if (! $sent) {
            $otp->delete();

            return ['ok' => false, 'error' => 'sms_failed'];
        }

        return ['ok' => true];
    }

    /**
     * @return array{ok: bool, error?: string, attempts_left?: int}
     *                                                              error: expired | invalid | too_many_attempts
     */
    public function verify(string $mobile, string $purpose, string $code): array
    {
        $max = config('portal.otp.max_attempts');

        $otp = OtpCode::where('mobile', $mobile)->where('purpose', $purpose)->whereNull('consumed_at')
            ->latest('id')->first();

        if (! $otp || $otp->expires_at->isPast()) {
            return ['ok' => false, 'error' => 'expired'];
        }

        if ($otp->attempts >= $max) {
            return ['ok' => false, 'error' => 'too_many_attempts'];
        }

        $otp->increment('attempts');

        if (! preg_match('/^\d+$/', $code) || ! Hash::check($code, $otp->code_hash)) {
            $left = $max - $otp->attempts;

            return $left > 0
                ? ['ok' => false, 'error' => 'invalid', 'attempts_left' => $left]
                : ['ok' => false, 'error' => 'too_many_attempts'];
        }

        $otp->update(['consumed_at' => now()]);

        return ['ok' => true];
    }

    public function secondsUntilResend(string $mobile, string $purpose): int
    {
        $last = OtpCode::where('mobile', $mobile)->where('purpose', $purpose)->latest('id')->value('created_at');

        if (! $last) {
            return 0;
        }

        $readyAt = $last->copy()->addSeconds(config('portal.otp.resend_cooldown'));

        return max(0, (int) ceil(now()->diffInSeconds($readyAt, false)));
    }

    /** Remove old codes (scheduled daily). */
    public function prune(): int
    {
        return OtpCode::where('created_at', '<', now()->subDay())->delete();
    }
}
