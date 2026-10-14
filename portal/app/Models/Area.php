<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/** A Jaipur area / locality, managed by admin (P05). */
class Area extends Model
{
    use HasTranslations;

    public const CACHE_KEY = 'areas.active';

    protected $fillable = ['name_en', 'name_hi', 'is_active', 'sort_order', 'is_sample'];

    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_sample' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name_en');
    }

    /** Active areas for dropdowns (cached; cleared when an area changes). */
    public static function activeList()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::active()->get(['id', 'name_en', 'name_hi']));
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
