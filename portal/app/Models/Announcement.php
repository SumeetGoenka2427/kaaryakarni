<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Homepage "Community Events & Announcements" item. */
class Announcement extends Model
{
    use BustsPublicCache, HasTranslations;

    public const TYPES = ['event' => 'Upcoming Event', 'notice' => 'Notice'];

    protected $fillable = ['type', 'title_en', 'title_hi', 'body_en', 'body_hi', 'event_date', 'starts_at', 'ends_at', 'sort_order', 'is_active'];

    protected array $translatable = ['title', 'body'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
    }

    /** Active and inside its optional display window. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')->orderByDesc('id');
    }
}
