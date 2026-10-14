<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * LOCAL DEVELOPMENT ONLY. Writes messages to storage/logs/otp.log instead of
 * sending them. The app refuses to boot in production with this driver.
 */
class LogSmsProvider implements SmsProvider
{
    public function send(string $mobile, string $message, ?string $templateId, array $vars): SmsResult
    {
        Log::channel('sms')->info("[DEV SMS] to {$mobile}: {$message}");

        return new SmsResult(true, 'log-'.Str::random(8));
    }

    public function name(): string
    {
        return 'log';
    }
}
