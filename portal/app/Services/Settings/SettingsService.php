<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-editable site settings.
 *
 * All settings are loaded with one query, cached forever and flushed on save,
 * so normal page views never hit the database for them. Keys ending in _en/_hi
 * are bilingual; read them with localized('org_name').
 */
class SettingsService
{
    private const CACHE_KEY = 'settings.all';

    /** Every setting with its default. Defaults mirror the final HTML design. */
    public const DEFAULTS = [
        // Organisation identity
        'org_name_en' => 'Khandelwal Samaj, Jaipur',
        'org_name_hi' => 'खंडेलवाल समाज, जयपुर',
        'org_short_name_en' => 'Khandelwal Samaj',
        'org_short_name_hi' => 'खंडेलवाल समाज',
        'tagline_en' => 'Serving the Khandelwal community of Jaipur through membership, welfare, business collaboration and cultural life.',
        'tagline_hi' => 'सदस्यता, कल्याण, व्यावसायिक सहयोग और सांस्कृतिक गतिविधियों के माध्यम से जयपुर के खंडेलवाल समुदाय की सेवा।',
        'founded_year' => null,

        // Office / contact
        'office_name_en' => 'Samaj Bhawan, Jaipur',
        'office_name_hi' => 'समाज भवन, जयपुर',
        'address_en' => 'Samaj Bhawan, Chandpole, Jaipur, Rajasthan',
        'address_hi' => 'समाज भवन, चांदपोल, जयपुर, राजस्थान',
        'phone_primary' => '+91 141 123 4567',
        'phone_secondary' => null,
        'whatsapp' => null,
        'email' => 'info@khandelwalsamajjaipur.org',
        'office_hours_en' => 'Monday – Saturday, 10:00 AM – 6:00 PM',
        'office_hours_hi' => 'सोमवार – शनिवार, सुबह 10:00 – शाम 6:00',
        'map_url' => null,

        // Social links
        'social_facebook' => null,
        'social_instagram' => null,
        'social_youtube' => null,

        // Membership & privacy (business rules)
        'membership_fee' => 5000,
        'donor_names_public' => true,
        'business_reapproval_on_edit' => true,

        // Logo display
        'show_name_beside_logo' => true,

        // Optional theme colour overrides (null = design default in tokens.css)
        'color_primary' => null,
        'color_gold' => null,
        'color_accent' => null,
    ];

    private ?array $loaded = null;

    public function all(): array
    {
        return $this->loaded ??= array_merge(self::DEFAULTS, Cache::rememberForever(self::CACHE_KEY, function () {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Setting::query()->pluck('value', 'key')->all();
        }));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /** Bilingual value for the current language, falling back to English. */
    public function localized(string $key, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $this->get("{$key}_{$locale}") ?: $this->get("{$key}_en");
    }

    /**
     * Save changed values. Returns [old, new] of what actually changed (for the audit log).
     *
     * @param  array<string, mixed>  $values
     * @return array{0: array, 1: array}
     */
    public function update(array $values): array
    {
        $current = $this->all();
        $old = $new = [];

        DB::transaction(function () use ($values, $current, &$old, &$new) {
            foreach ($values as $key => $value) {
                if (! array_key_exists($key, self::DEFAULTS) || $current[$key] === $value) {
                    continue;
                }
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
                $old[$key] = $current[$key];
                $new[$key] = $value;
            }
        });

        $this->flush();

        return [$old, $new];
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->loaded = null;
    }
}
