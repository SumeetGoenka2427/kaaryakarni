<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Left / right panel beside the homepage carousel.
 */
class BannerPanel extends Model
{
    use BustsPublicCache, HasTranslations;

    public const MODES = [
        'campaign_progress' => 'Live donation campaign — full (description, progress, donate)',
        'campaign_compact' => 'Live donation campaign — compact (collected, progress, donors, quick donate)',
        'membership_cta' => 'Lifetime membership call-to-action',
        'advertisement' => 'Advertisement (image + link)',
    ];

    /** Campaign metrics admin can switch on/off for campaign modes. */
    public const OPTIONS = [
        'show_target' => 'Show target amount',
        'show_percent' => 'Show progress percentage',
        'show_donors' => 'Show number of donors',
        'show_recent' => 'Show recent donations (names only if donor-name display is allowed)',
    ];

    protected $fillable = ['position', 'mode', 'ad_url', 'ad_label_en', 'ad_label_hi', 'is_active'];

    protected array $translatable = ['ad_label'];

    protected function casts(): array
    {
        return ['ad_image' => 'array', 'options' => 'array', 'is_active' => 'boolean'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DonationCampaign::class, 'campaign_id');
    }

    public function isCampaignMode(): bool
    {
        return str_starts_with($this->mode, 'campaign_');
    }

    public function option(string $key): bool
    {
        return (bool) (($this->options ?? [])[$key] ?? true);
    }

    public function safeAdUrl(): ?string
    {
        return safe_url($this->ad_url);
    }
}
