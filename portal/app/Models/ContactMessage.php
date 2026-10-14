<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Message from the public Contact form (Admin → Contact Messages). */
class ContactMessage extends Model
{
    public const SUBJECTS = ['membership', 'business', 'donation', 'matrimonial', 'general'];

    protected $fillable = ['name', 'mobile', 'email', 'subject', 'message', 'locale', 'ip'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'resolved_by');
    }

    public function subjectLabel(): string
    {
        return __('web.contact.subjects.'.$this->subject, [], 'en');
    }
}
