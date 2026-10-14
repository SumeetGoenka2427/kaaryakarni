<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Stores uploaded photos for the public site (banners, committee, page images).
 *
 * Each upload is decoded and re-encoded (strips EXIF/GPS and anything hidden
 * in the file) into WebP at several widths for responsive `srcset`. The
 * returned array is stored in a JSON column and rendered by <x-img>.
 */
class ImageService
{
    /** Validation rules for photo uploads (no SVG/GIF). */
    public static function rules(bool $required = false): array
    {
        return [$required ? 'required' : 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp',
            'max:'.config('portal.uploads.image_max_kb'), 'dimensions:min_width=200,min_height=150,max_width=8000,max_height=8000'];
    }

    /**
     * @param  list<int>  $widths  e.g. [480, 960, 1600]; never upscaled
     * @param  float|null  $ratio  crop to width/height ratio (e.g. 16/7 banners, 1 for avatars)
     * @return array{path: string, width: int, height: int, variants: array<int, string>}
     */
    public function store(UploadedFile $file, string $directory, array $widths, ?float $ratio = null): array
    {
        $manager = new ImageManager(new Driver);
        $source = $manager->read($file->getRealPath());

        if ($ratio) {
            $w = $source->width();
            $h = (int) round($w / $ratio);
            if ($h > $source->height()) {
                $h = $source->height();
                $w = (int) round($h * $ratio);
            }
            $source->cover($w, $h);
        }

        $base = trim($directory, '/').'/'.Str::lower(Str::ulid());
        $variants = [];
        $largest = null;

        foreach (array_unique($widths) as $width) {
            $target = min($width, $source->width());
            $image = (clone $source)->scaleDown(width: $target);
            $path = "{$base}-{$target}.webp";
            Storage::disk('public')->put($path, (string) $image->encode(new WebpEncoder(quality: 80)));
            $variants[$target] = $path;
            $largest = $image;
        }

        ksort($variants);

        return [
            'path' => end($variants),
            'width' => $largest->width(),
            'height' => $largest->height(),
            'variants' => $variants,
        ];
    }

    public function delete(?array $image): void
    {
        if ($image) {
            Storage::disk('public')->delete(array_values($image['variants'] ?? [$image['path'] ?? null]));
        }
    }
}
