<?php

namespace Tests\Support;

use App\Services\Sms\SmsProvider;
use App\Services\Sms\SmsResult;

/** Captures SMS in memory so tests can read the OTP that was "sent". */
class FakeSmsProvider implements SmsProvider
{
    /** @var list<array{mobile: string, message: string, vars: array}> */
    public array $sent = [];

    public bool $fail = false;

    public function send(string $mobile, string $message, ?string $templateId, array $vars): SmsResult
    {
        if ($this->fail) {
            return new SmsResult(false, error: 'gateway down');
        }

        $this->sent[] = compact('mobile', 'message', 'vars');

        return new SmsResult(true, 'fake-'.count($this->sent));
    }

    public function name(): string
    {
        return 'fake';
    }

    public function lastOtp(): ?string
    {
        $last = end($this->sent);

        return $last['vars']['otp'] ?? null;
    }
}
