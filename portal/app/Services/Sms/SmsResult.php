<?php

namespace App\Services\Sms;

final class SmsResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $messageId = null,
        public readonly ?string $error = null,
    ) {}
}
