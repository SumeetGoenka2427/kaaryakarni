<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'description', 'sms_en', 'sms_hi', 'dlt_template_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Message in the member's language with {placeholders} filled in. */
    public function render(string $locale, array $vars = []): string
    {
        $text = ($locale === 'hi' && filled($this->sms_hi)) ? $this->sms_hi : $this->sms_en;

        return strtr($text, collect($vars)->mapWithKeys(fn ($v, $k) => ['{'.$k.'}' => (string) $v])->all());
    }
}
