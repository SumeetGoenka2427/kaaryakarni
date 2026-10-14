<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\Sms\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

/** Background SMS (never used for OTPs). Retries with backoff on failure. */
class SendSms implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(
        public readonly int $smsLogId,
        public readonly string $templateKey,
        public readonly array $vars,
        public readonly string $locale,
    ) {}

    public function handle(SmsService $sms): void
    {
        $log = SmsLog::find($this->smsLogId);

        if ($log && ! $sms->deliverQueued($log, $this->templateKey, $this->vars, $this->locale)) {
            throw new RuntimeException("SMS {$this->smsLogId} failed; will retry.");
        }
    }
}
