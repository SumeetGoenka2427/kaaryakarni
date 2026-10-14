<?php

namespace App\Services\Sms;

/**
 * Any SMS gateway. Swap providers by adding a class and setting SMS_DRIVER;
 * no other code changes (CLAUDE.md §16).
 */
interface SmsProvider
{
    /**
     * @param  string  $mobile  10-digit Indian mobile number
     * @param  string  $message  Rendered text (for providers that send free text)
     * @param  string|null  $templateId  Provider / DLT template id
     * @param  array<string, string>  $vars  Template variables (for template-based providers)
     */
    public function send(string $mobile, string $message, ?string $templateId, array $vars): SmsResult;

    public function name(): string;
}
