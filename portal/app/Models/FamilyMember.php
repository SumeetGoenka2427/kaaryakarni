<?php

namespace App\Models;

use App\Enums\FamilyRelation;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A member's family member. Private data: only the owning member (scoped
 * queries + ULID route keys) and admins can read or change it.
 */
class FamilyMember extends Model
{
    use HasUlids, SoftDeletes;

    public const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    protected $fillable = [
        'relation', 'name', 'gender', 'dob', 'marital_status', 'education', 'occupation',
        'mobile', 'email', 'blood_group', 'is_deceased', 'lives_with_member', 'notes', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'relation' => FamilyRelation::class,
            'gender' => Gender::class,
            'marital_status' => MaritalStatus::class,
            'dob' => 'date',
            'photo' => 'array',
            'is_deceased' => 'boolean',
            'lives_with_member' => 'boolean',
        ];
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function age(): ?int
    {
        return $this->dob && ! $this->is_deceased ? $this->dob->age : null;
    }
}
