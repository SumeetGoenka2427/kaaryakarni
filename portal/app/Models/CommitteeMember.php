<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use BustsPublicCache, HasTranslations;

    public const TIERS = ['leadership' => 'Leadership (with photo)', 'general' => 'General Committee'];

    protected $fillable = ['name_en', 'name_hi', 'designation_en', 'designation_hi', 'tier', 'sort_order', 'is_active'];

    protected array $translatable = ['name', 'designation'];

    protected function casts(): array
    {
        return ['photo' => 'array', 'is_active' => 'boolean', 'is_sample' => 'boolean'];
    }

    /** "Shri R. Khandelwal" → "RK" (honorifics skipped). */
    public function initials(): string
    {
        return initials($this->name_en);
    }
}
