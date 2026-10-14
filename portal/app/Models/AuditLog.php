<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** Immutable record of an admin action. Written only via App\Services\Audit\AuditLogger. */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'old_values' => 'json',
            'new_values' => 'json',
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
