<?php

namespace App\Services\Branding;

use App\Models\BrandingAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Logos, favicon and social share image (Admin → Branding).
 *
 * Every upload is decoded and re-encoded server-side, which strips EXIF/GPS
 * data and anything embedded in the file, then resized for its placement.
 * SVG is deliberately not accepted (it can carry scripts).
 */
class BrandingService
{
    private const CACHE_KEY = 'branding.assets';

    /**
     * type => [label, help, max width, max height, output format, fit]
     * "contain" keeps the whole logo; "cover" crops to exact size.
     */
    public const TYPES = [
        'logo_header' => [
            'label' => 'Header Logo',
            'help' => 'Shown in the website header, member area and admin login (white background). Transparent PNG recommended.',
            'width' => 480, 'height' => 120, 'format' => 'webp', 'fit' => 'contain',
        ],
        'logo_footer' => [
            'label' => 'Footer / Dark Background Logo',
            'help' => 'Shown on the navy footer and the admin sidebar. Use a light or white version of the logo.',
            'width' => 480, 'height' => 120, 'format' => 'webp', 'fit' => 'contain',
        ],
        'logo_receipt' => [
            'label' => 'Receipt Logo',
            'help' => 'Printed on PDF receipts. PNG is generated automatically for PDF compatibility.',
            'width' => 600, 'height' => 200, 'format' => 'png', 'fit' => 'contain',
        ],
        'favicon' => [
            'label' => 'Favicon / App Icon',
            'help' => 'Square image, at least 192 × 192 px. Browser tab and phone home-screen icons are generated from it.',
            'width' => 192, 'height' => 192, 'format' => 'png', 'fit' => 'cover',
        ],
        'og_image' => [
            'label' => 'Social Share Image',
            'help' => 'Shown when the site is shared on WhatsApp / Facebook. 1200 × 630 px recommended.',
            'width' => 1200, 'height' => 630, 'format' => 'jpg', 'fit' => 'cover',
        ],
    ];

    /** Validation rules for an upload (images only, no SVG). */
    public static function rules(): array
    {
        return ['required', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:'.config('portal.uploads.logo_max_kb'), 'dimensions:min_width=64,min_height=64,max_width=6000,max_height=6000'];
    }

    /** @return array<string, BrandingAsset> */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            if (! Schema::hasTable('branding_assets')) {
                return [];
            }

            return BrandingAsset::all()->keyBy('type')->all();
        });
    }

    public function get(string $type): ?BrandingAsset
    {
        return $this->all()[$type] ?? null;
    }

    public function url(string $type): ?string
    {
        return $this->get($type)?->url();
    }

    public function store(string $type, UploadedFile $file): BrandingAsset
    {
        $spec = self::TYPES[$type];
        $image = (new ImageManager(new Driver))->read($file->getRealPath());

        $spec['fit'] === 'cover'
            ? $image->cover($spec['width'], $spec['height'])
            : $image->scaleDown($spec['width'], $spec['height']);

        $encoded = match ($spec['format']) {
            'png' => $image->encode(new PngEncoder),
            'jpg' => $image->encode(new JpegEncoder(quality: 82, progressive: true)),
            default => $image->encode(new WebpEncoder(quality: 90)),
        };

        $existing = BrandingAsset::where('type', $type)->first();
        $version = ($existing?->version ?? 0) + 1;
        $path = "branding/{$type}-v{$version}.{$spec['format']}";

        Storage::disk('public')->put($path, (string) $encoded);

        if ($type === 'favicon') {
            // Browser tab + iOS home-screen sizes.
            foreach ([32, 180] as $size) {
                $icon = (new ImageManager(new Driver))->read((string) $encoded)->cover($size, $size);
                Storage::disk('public')->put("branding/favicon-{$size}-v{$version}.png", (string) $icon->encode(new PngEncoder));
            }
        }

        if ($existing) {
            $this->deleteFiles($existing);
        }

        $asset = BrandingAsset::updateOrCreate(['type' => $type], [
            'path' => $path,
            'width' => $image->width(),
            'height' => $image->height(),
            'version' => $version,
        ]);

        $this->flush();

        return $asset;
    }

    public function remove(string $type): ?BrandingAsset
    {
        $asset = BrandingAsset::where('type', $type)->first();

        if ($asset) {
            $this->deleteFiles($asset);
            $asset->delete();
            $this->flush();
        }

        return $asset;
    }

    /** Favicon size variant URL (32, 180) or the main 192px icon. */
    public function faviconUrl(int $size = 192): ?string
    {
        $asset = $this->get('favicon');

        if (! $asset) {
            return null;
        }

        return $size === 192
            ? $asset->url()
            : Storage::disk('public')->url("branding/favicon-{$size}-v{$asset->version}.png").'?v='.$asset->version;
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function deleteFiles(BrandingAsset $asset): void
    {
        Storage::disk('public')->delete(array_merge([$asset->path], $asset->type === 'favicon' ? [
            "branding/favicon-32-v{$asset->version}.png",
            "branding/favicon-180-v{$asset->version}.png",
        ] : []));
    }
}
