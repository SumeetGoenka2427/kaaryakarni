<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Enums\MembershipStatus;
use App\Services\Membership\MembershipService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A member's one-time lifetime membership. There is deliberately no expiry,
 * renewal date or recurring amount anywhere in this model.
 */
class Membership extends Model implements Payable
{
    protected $fillable = ['member_id', 'fee_amount'];

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'fee_amount' => 'decimal:2',
            'activated_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::Active;
    }

    /** Fee can be paid when nothing is paid or awaiting verification yet. */
    public function canPay(): bool
    {
        return $this->status === MembershipStatus::PaymentPending;
    }

    public function paymentSucceeded(Payment $payment): void
    {
        app(MembershipService::class)->paymentConfirmed($this, $payment);
    }

    public function paymentSubmitted(Payment $payment): void
    {
        $this->status = MembershipStatus::PaymentSubmitted;
        $this->payment_id = $payment->id;
        $this->save();
    }

    public function paymentFailed(Payment $payment): void
    {
        // Back to "pay now" unless another payment already confirmed it.
        if (! $this->isActive() && $this->status !== MembershipStatus::UnderVerification) {
            $this->status = MembershipStatus::PaymentPending;
            $this->save();
        }
    }

    public function paymentDescription(): string
    {
        return 'Lifetime Membership Fee';
    }
}
