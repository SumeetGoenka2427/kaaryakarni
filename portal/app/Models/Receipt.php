<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A numbered receipt for a completed payment. Issued only by ReceiptService. */
class Receipt extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
