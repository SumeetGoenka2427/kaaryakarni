<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A member's report about a matrimonial profile (Admin → Matrimonial → Reports). */
class MatrimonialReport extends Model
{
    public const REASONS = ['fake', 'inappropriate', 'married', 'misuse', 'other'];

    protected $fillable = ['reason', 'details'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(MatrimonialProfile::class, 'profile_id')->withTrashed();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'reporter_id');
    }
}
