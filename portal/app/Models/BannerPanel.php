<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

/**
 * Left / right panel beside the homepage carousel.
 * Donation-campaign display modes are added with campaigns in P07.
 */
class BannerPanel extends Model
{
    use BustsPublicCache, HasTranslations;

    public const MODES = [
        'membership_cta' => 'Lifetime membership call-to-action',
        'advertisement' => 'Advertisement (image + link)',
    ];

    protected $fillable = ['position', 'mode', 'ad_url', 'ad_label_en', 'ad_label_hi', 'is_active'];

    protected array $translatable = ['ad_label'];

    protected function casts(): array
    {
        return ['ad_image' => 'array', 'options' => 'array', 'is_active' => 'boolean'];
    }

    public function safeAdUrl(): ?string
    {
        return safe_url($this->ad_url);
    }
}
