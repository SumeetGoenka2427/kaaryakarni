<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A Samaj member. Logs in with mobile + OTP on the `member` guard.
 * Status changes (verify / reject / suspend) happen only through admin
 * services — `status` is never mass-assignable from a request.
 */
class Member extends Authenticatable
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'name', 'father_or_husband_name', 'mobile', 'email', 'dob', 'gender',
        'address', 'area_id', 'locale',
    ];

    protected $hidden = ['remember_token'];

    protected function casts(): array
    {
        return [
            'status' => MemberStatus::class,
            'gender' => Gender::class,
            'dob' => 'date',
            'consented_at' => 'datetime',
            'profile_submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'last_login_at' => 'datetime',
            'photo' => 'array',
        ];
    }

    /** ULIDs live in the `ulid` column; the numeric id stays the primary key. */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /** Members log in with OTP only — there is no password. */
    public function getAuthPassword(): string
    {
        return '';
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function family(): HasMany
    {
        return $this->hasMany(FamilyMember::class)->orderBy('sort_order')->orderBy('id');
    }

    public function business(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function matrimonialProfiles(): HasMany
    {
        return $this->hasMany(MatrimonialProfile::class)->latest('id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(MemberVerification::class)->latest('id');
    }

    public function hasCompletedProfile(): bool
    {
        return $this->profile_submitted_at !== null;
    }

    public function isSuspended(): bool
    {
        return $this->status === MemberStatus::Suspended;
    }

    public function firstName(): string
    {
        return explode(' ', trim((string) $this->name))[0] ?: (string) $this->mobile;
    }
}
