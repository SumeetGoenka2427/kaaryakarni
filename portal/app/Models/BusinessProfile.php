<?php

namespace App\Models;

use App\Enums\BusinessStatus;
use App\Models\Concerns\BustsPublicCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A member's business / professional listing. Only `approved` listings are
 * public. Status, featured flag and admin notes change only via BusinessService.
 */
class BusinessProfile extends Model
{
    use BustsPublicCache, SoftDeletes;

    /** Changing these on an approved listing can send it back for approval (setting). */
    public const KEY_FIELDS = ['name', 'category_id', 'description'];

    protected $fillable = [
        'category_id', 'area_id', 'name', 'description', 'address', 'phone', 'whatsapp', 'email',
        'website', 'hours', 'show_phone', 'show_whatsapp', 'show_email',
    ];

    protected function casts(): array
    {
        return [
            'status' => BusinessStatus::class,
            'logo' => 'array',
            'show_phone' => 'boolean',
            'show_whatsapp' => 'boolean',
            'show_email' => 'boolean',
            'is_featured' => 'boolean',
            'is_sample' => 'boolean',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', BusinessStatus::Approved);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'category_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function isPublic(): bool
    {
        return $this->status === BusinessStatus::Approved;
    }

    public function safeWebsite(): ?string
    {
        return safe_url($this->website);
    }

    /** Google Maps search for the address (no API key needed). */
    public function mapQuery(): ?string
    {
        return $this->address ? $this->address.', '.($this->area?->name_en ?? '').', Jaipur' : null;
    }
}
