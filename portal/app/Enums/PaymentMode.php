<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * How the money was paid.
 */
enum PaymentMode: string
{
    use HasLabel;

    case Online = 'online';
    case Offline = 'offline';
}
