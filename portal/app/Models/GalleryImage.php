<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** One photo in a gallery album (responsive WebP from ImageService, public disk). */
class GalleryImage extends Model
{
    use BustsPublicCache, HasTranslations;

    protected $fillable = ['caption_en', 'caption_hi', 'sort_order'];

    protected array $translatable = ['caption'];

    protected function casts(): array
    {
        return ['image' => 'array'];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'album_id');
    }

    /** Largest variant URL, used by the lightbox. */
    public function fullUrl(): string
    {
        return Storage::disk('public')->url($this->image['path']);
    }

    /** "url 480w, url 960w, …" for the lightbox's responsive image. */
    public function srcset(): string
    {
        $disk = Storage::disk('public');

        return collect($this->image['variants'] ?? [])->map(fn ($p, $w) => $disk->url($p).' '.$w.'w')->implode(', ');
    }
}
