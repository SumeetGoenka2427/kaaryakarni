<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Business directory category (Admin → Business Categories), EN/HI. */
class BusinessCategory extends Model
{
    use BustsPublicCache, HasTranslations;

    protected $fillable = ['slug', 'name_en', 'name_hi', 'is_active', 'sort_order'];

    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(BusinessProfile::class, 'category_id');
    }
}
