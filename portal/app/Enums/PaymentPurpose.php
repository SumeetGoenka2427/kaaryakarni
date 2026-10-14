<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * What a payment is for.
 */
enum PaymentPurpose: string
{
    use HasLabel;

    case Membership = 'membership';
    case Donation = 'donation';
    case Ticket = 'ticket';
}
