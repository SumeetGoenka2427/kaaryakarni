<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

/** Bilingual content block on a public page. Allowed pages/sections: config/content.php */
class ContentBlock extends Model
{
    use BustsPublicCache, HasTranslations;

    protected $fillable = [
        'page', 'section', 'label_en', 'label_hi', 'title_en', 'title_hi', 'body_en', 'body_hi',
        'link_url', 'sort_order', 'is_active',
    ];

    protected array $translatable = ['label', 'title', 'body'];

    protected function casts(): array
    {
        return ['image' => 'array', 'is_active' => 'boolean'];
    }

    /** Body split into paragraphs / list lines (blank lines separate paragraphs). */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string) $this->t('body')))));
    }

    public function lines(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->t('body')))));
    }

    public function safeLinkUrl(): ?string
    {
        return safe_url($this->link_url);
    }
}
