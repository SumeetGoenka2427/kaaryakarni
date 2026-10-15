<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A matrimonial profile photo (private disk, streamed after an access check). */
class MatrimonialPhoto extends Model
{
    protected $fillable = ['sort_order'];

    protected function casts(): array
    {
        return ['image' => 'array'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(MatrimonialProfile::class, 'profile_id');
    }
}
