<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\MatrimonialStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Private matrimonial profile. Status / admin notes change only through
 * MatrimonialService; photos and bio-data live on the private disk.
 */
class MatrimonialProfile extends Model
{
    use HasUlids, SoftDeletes;

    public const MAX_PER_MEMBER = 3;

    public const MAX_PHOTOS = 4;

    public const RELATIONS = ['self', 'son', 'daughter', 'brother', 'sister', 'other'];

    protected $fillable = [
        'relation', 'name', 'gender', 'dob', 'height_cm', 'marital_status', 'father_name', 'mother_name',
        'gotra', 'native_place', 'area_id', 'education', 'occupation', 'about', 'show_contact',
    ];

    protected function casts(): array
    {
        return [
            'status' => MatrimonialStatus::class,
            'gender' => Gender::class,
            'marital_status' => MaritalStatus::class,
            'dob' => 'date',
            'bio_data' => 'array',
            'show_contact' => 'boolean',
            'is_sample' => 'boolean',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
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

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', MatrimonialStatus::Approved);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MatrimonialPhoto::class, 'profile_id')->orderBy('sort_order')->orderBy('id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MatrimonialReport::class, 'profile_id');
    }

    public function age(): int
    {
        return $this->dob->age;
    }

    /** "5'6\"" from centimetres. */
    public function heightLabel(): ?string
    {
        return $this->height_cm ? self::cmToFeet($this->height_cm) : null;
    }

    public static function cmToFeet(int $cm): string
    {
        $inches = (int) round($cm / 2.54);

        return intdiv($inches, 12)."'".($inches % 12).'"';
    }

    /** Height options 4'6" – 6'6" for selects: cm => label. */
    public static function heightOptions(): array
    {
        $options = [];
        for ($in = 54; $in <= 78; $in++) {
            $cm = (int) round($in * 2.54);
            $options[$cm] = intdiv($in, 12)."'".($in % 12).'" ('.$cm.' cm)';
        }

        return $options;
    }
}
