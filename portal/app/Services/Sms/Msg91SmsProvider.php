<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * MSG91 Flow API (DLT-compliant template SMS).
 * Each notification template stores its MSG91 flow/template id; variables are
 * passed by name, so the message text itself never leaves our server.
 */
class Msg91SmsProvider implements SmsProvider
{
    private const ENDPOINT = 'https://control.msg91.com/api/v5/flow';

    public function __construct(private readonly array $config) {}

    public function send(string $mobile, string $message, ?string $templateId, array $vars): SmsResult
    {
        if (blank($this->config['auth_key']) || blank($templateId)) {
            return new SmsResult(false, error: 'MSG91 auth key or template id is not configured.');
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 300, throw: false)
                ->withHeaders(['authkey' => $this->config['auth_key'], 'accept' => 'application/json'])
                ->post(self::ENDPOINT, [
                    'template_id' => $templateId,
                    'short_url' => '0',
                    'recipients' => [array_merge(['mobiles' => '91'.$mobile], $vars)],
                ]);

            if ($response->successful() && $response->json('type') === 'success') {
                return new SmsResult(true, (string) $response->json('message'));
            }

            return new SmsResult(false, error: 'MSG91 HTTP '.$response->status().': '.mb_substr((string) $response->json('message', $response->body()), 0, 200));
        } catch (Throwable $e) {
            return new SmsResult(false, error: mb_substr($e->getMessage(), 0, 200));
        }
    }

    public function name(): string
    {
        return 'msg91';
    }
}
