<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One verification decision in a member's history. Written only by MemberService. */
class MemberVerification extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['member_id', 'action', 'reason', 'admin_user_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }
}
