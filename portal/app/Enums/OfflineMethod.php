<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

/**
 * Method used for an offline payment.
 */
enum OfflineMethod: string
{
    use HasLabel;

    case Cash = 'cash';
    case Cheque = 'cheque';
    case Upi = 'upi';
    case BankTransfer = 'bank_transfer';
}
