<?php

namespace App\Services\Gallery;

use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Services\Audit\AuditLogger;
use App\Services\Media\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Gallery albums and images (Admin → Gallery). Keeps the album's cover and
 * image count in step with its images so public pages need no extra queries.
 */
class GalleryService
{
    /** Responsive widths: grid tiles use 480, lightbox up to 1600. */
    public const WIDTHS = [480, 960, 1600];

    public function __construct(
        private readonly ImageService $images,
        private readonly AuditLogger $audit,
    ) {}

    public function saveAlbum(GalleryAlbum $album, array $data, bool $active): GalleryAlbum
    {
        $creating = ! $album->exists;
        $old = $album->only(['title_en', 'is_active']);
        $album->fill($data);
        $album->sort_order = (int) ($data['sort_order'] ?? 0);
        $album->is_active = $active;
        if ($creating || $album->isDirty('title_en')) {
            $album->slug = $this->uniqueSlug($album->title_en, $album->id);
        }
        $album->save();

        $creating
            ? $this->audit->record('gallery.album_created', $album, "Created gallery album {$album->title_en}")
            : $this->audit->record('gallery.album_updated', $album, "Updated gallery album {$album->title_en}", $old, $album->only(array_keys($old)));

        return $album;
    }

    public function toggle(GalleryAlbum $album): void
    {
        $album->is_active = ! $album->is_active;
        $album->save();
        $this->audit->record('gallery.album_toggled', $album, ($album->is_active ? 'Activated' : 'Deactivated')." gallery album {$album->title_en}");
    }

    public function deleteAlbum(GalleryAlbum $album): void
    {
        $album->images()->get()->each(fn (GalleryImage $img) => $this->images->delete($img->image));
        $album->delete(); // images cascade in the database
        $this->audit->record('gallery.album_deleted', null, "Deleted gallery album {$album->title_en}");
    }

    /** @param list<UploadedFile> $files */
    public function addImages(GalleryAlbum $album, array $files): int
    {
        $next = (int) $album->images()->max('sort_order');
        $added = 0;
        foreach ($files as $file) {
            $img = new GalleryImage(['sort_order' => ++$next]);
            $img->image = $this->images->store($file, 'gallery', self::WIDTHS);
            $album->images()->save($img);
            $added++;
        }
        $this->refresh($album);
        $this->audit->record('gallery.images_added', $album, "Added {$added} photo(s) to gallery album {$album->title_en}");

        return $added;
    }

    public function updateCaption(GalleryImage $image, array $data): void
    {
        $image->fill($data)->save();
    }

    /** Swap with the neighbouring image ('up' = earlier) and renumber 1..n. */
    public function move(GalleryImage $image, string $direction): void
    {
        $ids = GalleryImage::where('album_id', $image->album_id)->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
        $i = array_search($image->id, $ids, true);
        $j = $direction === 'up' ? $i - 1 : $i + 1;
        if ($i === false || ! isset($ids[$j])) {
            return;
        }
        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];

        DB::transaction(function () use ($ids) {
            foreach ($ids as $pos => $id) {
                GalleryImage::whereKey($id)->update(['sort_order' => $pos + 1]);
            }
        });
        GalleryAlbum::whereKey($image->album_id)->first()?->touch(); // query-builder updates skip model events: refresh the public cache
    }

    public function setCover(GalleryImage $image): void
    {
        $album = $image->album()->first();
        $album->cover_image_id = $image->id;
        $album->save();
        $this->audit->record('gallery.cover_set', $album, "Changed cover photo of gallery album {$album->title_en}");
    }

    public function deleteImage(GalleryImage $image): void
    {
        $album = $image->album()->first();
        $this->images->delete($image->image);
        $image->delete();
        $this->refresh($album);
        $this->audit->record('gallery.image_deleted', $album, "Deleted a photo from gallery album {$album->title_en}");
    }

    /** Recount images and make sure the cover points at an existing image. */
    private function refresh(GalleryAlbum $album): void
    {
        $album->images_count = $album->images()->count();
        if (! $album->cover_image_id || ! $album->images()->whereKey($album->cover_image_id)->exists()) {
            $album->cover_image_id = $album->images()->value('id');
        }
        $album->save();
    }

    private function uniqueSlug(string $title, ?int $ignoreId): string
    {
        $base = Str::limit(Str::slug($title) ?: 'album', 120, '');
        $slug = $base;
        $i = 2;
        while (GalleryAlbum::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
