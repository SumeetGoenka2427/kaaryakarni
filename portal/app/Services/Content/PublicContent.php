<?php

namespace App\Services\Content;

use App\Enums\DonationStatus;
use App\Enums\MemberStatus;
use App\Models\Announcement;
use App\Models\Area;
use App\Models\BannerPanel;
use App\Models\BannerSlide;
use App\Models\BusinessProfile;
use App\Models\CommitteeMember;
use App\Models\ContentBlock;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Member;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Cached read access to admin-managed public content.
 *
 * Everything is cached under one version number. Saving or deleting any
 * content model (BustsPublicCache) bumps the version, so visitors never see
 * stale content and normal page views do no content queries.
 */
class PublicContent
{
    private const VERSION_KEY = 'public_content.version';

    private const TTL = 3600; // safety net for time-based content (scheduled slides / announcements)

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, (int) Cache::get(self::VERSION_KEY, 1) + 1);
    }

    /** Blocks of one page section, ordered. */
    public function blocks(string $page, string $section): Collection
    {
        return $this->pageBlocks($page)->get($section, collect());
    }

    public function block(string $page, string $section): ?ContentBlock
    {
        return $this->blocks($page, $section)->first();
    }

    public function slides(): Collection
    {
        return $this->remember('slides', fn () => BannerSlide::visible()->get());
    }

    public function panels(): Collection
    {
        return $this->remember('panels', fn () => BannerPanel::where('is_active', true)->with('campaign')->get()->keyBy('position'));
    }

    /** Approved businesses for the homepage: featured first. */
    public function featuredBusinesses(int $limit = 4): Collection
    {
        return $this->remember("businesses.featured.{$limit}", fn () => BusinessProfile::approved()
            ->with(['category:id,name_en,name_hi', 'area:id,name_en,name_hi'])
            ->orderByDesc('is_featured')->latest('approved_at')->limit($limit)->get());
    }

    /** Open donation campaigns, permanent "Samaj Donation" first. */
    public function campaigns(): Collection
    {
        return $this->remember('campaigns', fn () => DonationCampaign::open()->get());
    }

    /** Latest confirmed donations for a campaign (names shown only with consent — see Donation::publicName). */
    public function recentDonations(int $campaignId, int $limit = 5): Collection
    {
        return $this->remember("recent_donations.{$campaignId}.{$limit}", fn () => Donation::where('campaign_id', $campaignId)
            ->whereIn('status', [DonationStatus::Successful, DonationStatus::Verified])
            ->latest('updated_at')->limit($limit)->get(['id', 'donor_name', 'is_anonymous', 'amount', 'type', 'updated_at']));
    }

    public function announcements(int $limit = 3): Collection
    {
        return $this->remember("announcements.{$limit}", fn () => Announcement::visible()->limit($limit)->get());
    }

    public function committee(string $tier): Collection
    {
        return $this->remember("committee.{$tier}", fn () => CommitteeMember::where('tier', $tier)->where('is_active', true)
            ->orderBy('sort_order')->orderBy('name_en')->get());
    }

    /**
     * Homepage statistics from real data (CLAUDE.md §28 — no hard-coded numbers).
     * Lifetime-member and donation figures join in with P06/P07.
     *
     * @return list<array{value: string, label: string}>
     */
    public function stats(): array
    {
        $raw = Cache::remember('public_content.stats', 600, fn () => [
            'members' => Member::where('status', MemberStatus::Verified)->count(),
            'areas' => Area::where('is_active', true)->count(),
            'donations' => (float) DonationCampaign::withTrashed()->sum('raised_amount'),
            'businesses' => BusinessProfile::approved()->count(),
        ]);

        // A statistic is shown only once it has a real, non-zero value.
        $stats = array_values(array_filter([
            $raw['members'] > 0 ? ['value' => number_format($raw['members']), 'label' => __('web.home.stat_members')] : null,
            $raw['areas'] > 0 ? ['value' => (string) $raw['areas'], 'label' => __('web.home.stat_areas')] : null,
            ($raw['businesses'] ?? 0) > 0 ? ['value' => number_format($raw['businesses']), 'label' => __('web.home.stat_businesses')] : null,
            ($raw['donations'] ?? 0) > 0 ? ['value' => inr_short($raw['donations']), 'label' => __('web.home.stat_donations')] : null,
        ]));

        if ($founded = setting('founded_year')) {
            $stats[] = ['value' => (now()->year - (int) $founded).'+', 'label' => __('web.home.stat_years')];
        }

        return $stats;
    }

    private function pageBlocks(string $page): Collection
    {
        return $this->remember("blocks.{$page}", fn () => ContentBlock::where('page', $page)->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')->get()->groupBy('section'));
    }

    private function remember(string $key, callable $callback): mixed
    {
        $version = (int) Cache::get(self::VERSION_KEY, 1);

        return Cache::remember("public_content.v{$version}.{$key}", self::TTL, $callback);
    }
}
