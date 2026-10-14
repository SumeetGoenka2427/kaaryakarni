<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * A donation campaign. The permanent "Samaj Donation" campaign can never be
 * deleted or deactivated — enforced here at model level, not just in the UI.
 */
class DonationCampaign extends Model
{
    use BustsPublicCache, HasTranslations, SoftDeletes;

    protected $fillable = [
        'slug', 'title_en', 'title_hi', 'description_en', 'description_hi', 'target_amount',
        'allow_online', 'allow_offline', 'has_tickets', 'ticket_label_en', 'ticket_label_hi', 'ticket_price',
        'starts_at', 'ends_at', 'sort_order',
    ];

    protected array $translatable = ['title', 'description', 'ticket_label'];

    protected function casts(): array
    {
        return [
            'image' => 'array',
            'target_amount' => 'decimal:2',
            'raised_amount' => 'decimal:2',
            'ticket_price' => 'decimal:2',
            'is_permanent' => 'boolean',
            'is_active' => 'boolean',
            'allow_online' => 'boolean',
            'allow_offline' => 'boolean',
            'has_tickets' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $campaign) {
            if ($campaign->is_permanent) {
                throw new LogicException('The permanent Samaj Donation campaign cannot be deleted.');
            }
        });
        static::saving(function (self $campaign) {
            if ($campaign->is_permanent && ! $campaign->is_active) {
                throw new LogicException('The permanent Samaj Donation campaign cannot be deactivated.');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'campaign_id');
    }

    /** Active and inside its optional date window, permanent campaign first. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', today()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', today()))
            ->orderByDesc('is_permanent')->orderBy('sort_order')->orderBy('id');
    }

    public function isOpen(): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte(today()))
            && (! $this->ends_at || $this->ends_at->gte(today()));
    }

    public function progressPercent(): ?int
    {
        if (! $this->target_amount || (float) $this->target_amount <= 0) {
            return null;
        }

        return (int) min(100, floor((float) $this->raised_amount / (float) $this->target_amount * 100));
    }

    public function sellsTickets(): bool
    {
        return $this->has_tickets && (float) $this->ticket_price > 0;
    }
}
