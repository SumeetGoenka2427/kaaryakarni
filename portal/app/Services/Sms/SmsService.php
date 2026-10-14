<?php

namespace App\Services\Sms;

use App\Jobs\SendSms;
use App\Models\NotificationTemplate;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;

/**
 * Sends template SMS in the member's language and records delivery in
 * sms_logs (without the text).
 *
 * - sendNow(): synchronous — used for OTPs so the code is never written to
 *   the jobs table.
 * - queue(): background job with retries — used for confirmations/approvals.
 */
class SmsService
{
    public function __construct(private readonly SmsProvider $provider) {}

    public function sendNow(string $mobile, string $templateKey, array $vars = [], string $locale = 'en'): bool
    {
        $template = $this->template($templateKey);
        if (! $template) {
            return false;
        }

        $log = SmsLog::create([
            'mobile' => $mobile,
            'template_key' => $templateKey,
            'provider' => $this->provider->name(),
            'status' => 'sending',
        ]);

        return $this->deliver($log, $template->render($locale, $vars), $template->dlt_template_id, $vars);
    }

    public function queue(string $mobile, string $templateKey, array $vars = [], string $locale = 'en'): void
    {
        if (! $this->template($templateKey)) {
            return;
        }

        $log = SmsLog::create([
            'mobile' => $mobile,
            'template_key' => $templateKey,
            'provider' => $this->provider->name(),
            'status' => 'queued',
        ]);

        SendSms::dispatch($log->id, $templateKey, $vars, $locale);
    }

    /** Used by the SendSms job. */
    public function deliverQueued(SmsLog $log, string $templateKey, array $vars, string $locale): bool
    {
        $template = $this->template($templateKey);

        return $template ? $this->deliver($log, $template->render($locale, $vars), $template->dlt_template_id, $vars) : false;
    }

    private function deliver(SmsLog $log, string $text, ?string $templateId, array $vars): bool
    {
        $result = $this->provider->send($log->mobile, $text, $templateId, array_map('strval', $vars));

        $log->update([
            'status' => $result->ok ? 'sent' : 'failed',
            'provider_message_id' => $result->messageId,
            'error' => $result->error,
        ]);

        if (! $result->ok) {
            Log::warning('SMS delivery failed', ['sms_log_id' => $log->id, 'template' => $log->template_key, 'error' => $result->error]);
        }

        return $result->ok;
    }

    private function template(string $key): ?NotificationTemplate
    {
        $template = NotificationTemplate::where('key', $key)->where('is_active', true)->first();

        if (! $template) {
            Log::warning("SMS template [{$key}] is missing or inactive.");
        }

        return $template;
    }
}
