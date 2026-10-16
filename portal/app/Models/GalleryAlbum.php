<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Gallery album (Admin → Gallery). Public pages show active albums with at least one image. */
class GalleryAlbum extends Model
{
    use BustsPublicCache, HasTranslations;

    public const MAX_UPLOAD = 20;

    protected $fillable = ['title_en', 'title_hi', 'description_en', 'description_hi', 'event_date', 'sort_order'];

    protected array $translatable = ['title', 'description'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'is_active' => 'boolean', 'is_sample' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Active albums that have photos, in admin order (newest event first on ties). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('images_count', '>', 0)
            ->orderBy('sort_order')->orderByDesc('event_date')->orderByDesc('id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class, 'album_id')->orderBy('sort_order')->orderBy('id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(GalleryImage::class, 'cover_image_id');
    }
}
