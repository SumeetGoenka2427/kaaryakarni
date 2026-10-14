<?php

namespace App\Models\Concerns;

/**
 * Admin-managed bilingual content stored as paired columns: title_en / title_hi.
 *
 *   $campaign->t('title')        → Hindi on /hi pages, English otherwise
 *   $campaign->t('title', 'en')  → a specific language
 *
 * English is required and Hindi is optional: an empty Hindi value falls back
 * to English so a page never shows a blank heading. Models list their
 * bilingual fields in `protected array $translatable = ['title', ...]`.
 */
trait HasTranslations
{
    public function t(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $fallback = array_key_first(config('portal.locales'));

        $value = $this->getAttribute("{$field}_{$locale}");

        if ($value === null || $value === '') {
            $value = $this->getAttribute("{$field}_{$fallback}");
        }

        return $value;
    }

    /** True when any translatable field has English text but no Hindi ("HI missing" badge in admin). */
    public function isMissingTranslation(string $locale = 'hi'): bool
    {
        foreach ($this->translatableFields() as $field) {
            if (filled($this->getAttribute("{$field}_en")) && blank($this->getAttribute("{$field}_{$locale}"))) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function translatableFields(): array
    {
        return $this->translatable ?? [];
    }
}
