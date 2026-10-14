<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A hashed one-time password. Managed only by App\Services\Otp\OtpService. */
class OtpCode extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consumed_at' => 'datetime', 'created_at' => 'datetime'];
    }
}
