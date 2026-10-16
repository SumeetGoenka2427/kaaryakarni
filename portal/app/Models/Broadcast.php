<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An admin announcement sent to members' notification inboxes (and optionally by SMS). */
class Broadcast extends Model
{
    use HasTranslations;

    public const AUDIENCES = ['all' => 'All members', 'lifetime' => 'Lifetime members only', 'area' => 'Members of one area'];

    public const TYPES = ['announcement' => 'Announcement', 'event' => 'Event', 'membership' => 'Membership', 'donation' => 'Donation', 'business' => 'Business'];

    protected $fillable = ['title_en', 'title_hi', 'message_en', 'message_hi', 'type', 'audience', 'area_id', 'send_sms'];

    protected array $translatable = ['title', 'message'];

    protected function casts(): array
    {
        return ['send_sms' => 'boolean'];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'sent_by');
    }

    public function audienceLabel(): string
    {
        return $this->audience === 'area' ? 'Area: '.($this->area?->name_en ?? '—') : (self::AUDIENCES[$this->audience] ?? $this->audience);
    }
}
