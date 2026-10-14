<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A gateway callback / webhook received for a payment (audit + idempotency). */
class PaymentEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'signature_valid' => 'boolean', 'created_at' => 'datetime'];
    }
}
