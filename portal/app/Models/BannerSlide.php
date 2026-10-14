<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Homepage center carousel slide (Admin → Homepage Banners). */
class BannerSlide extends Model
{
    use BustsPublicCache, HasTranslations;

    protected $fillable = [
        'title_en', 'title_hi', 'subtitle_en', 'subtitle_hi', 'cta_label_en', 'cta_label_hi', 'cta_url',
        'sort_order', 'is_active', 'starts_at', 'ends_at',
    ];

    protected array $translatable = ['title', 'subtitle', 'cta_label'];

    protected function casts(): array
    {
        return ['image' => 'array', 'is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    /** Active and inside the optional start/end schedule, in display order. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')->orderBy('id');
    }

    /** Scheduling status shown in the admin list. */
    public function displayStatus(): string
    {
        return match (true) {
            ! $this->is_active => 'Inactive',
            (bool) $this->starts_at?->isFuture() => 'Scheduled',
            (bool) $this->ends_at?->isPast() => 'Ended',
            default => 'Live',
        };
    }

    public function safeCtaUrl(): ?string
    {
        return safe_url($this->cta_url);
    }
}
