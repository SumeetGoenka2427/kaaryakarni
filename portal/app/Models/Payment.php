<?php

namespace App\Models;

use App\Enums\OfflineMethod;
use App\Enums\PaymentMode;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A payment. Status changes happen only in App\Services\Payments\PaymentService
 * (never from request input), so nothing here is mass-assignable except
 * descriptive offline details.
 */
class Payment extends Model
{
    use HasUlids;

    protected $fillable = ['offline_method', 'offline_reference', 'offline_paid_on', 'payer_name', 'payer_mobile', 'payer_email'];

    protected function casts(): array
    {
        return [
            'purpose' => PaymentPurpose::class,
            'mode' => PaymentMode::class,
            'status' => PaymentStatus::class,
            'offline_method' => OfflineMethod::class,
            'offline_paid_on' => 'date',
            'amount' => 'decimal:2',
            'proof' => 'array',
            'meta' => 'array',
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'verified_by');
    }

    public function isComplete(): bool
    {
        return in_array($this->status, [PaymentStatus::Successful, PaymentStatus::Verified], true);
    }

    public function isFinal(): bool
    {
        return $this->isComplete() || in_array($this->status, [PaymentStatus::Refunded, PaymentStatus::Cancelled], true);
    }

    /** Name shown on receipts / lists: member name, else the payer's name. */
    public function displayName(): string
    {
        return $this->member?->name ?? $this->payer_name ?? '—';
    }

    public function reference(): ?string
    {
        return $this->provider_payment_id ?? $this->offline_reference;
    }
}
