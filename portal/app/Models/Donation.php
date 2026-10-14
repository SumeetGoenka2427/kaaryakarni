<?php

namespace App\Models;

use App\Contracts\Payable;
use App\Enums\DonationStatus;
use App\Enums\PaymentMode;
use App\Services\Content\PublicContent;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * A donation or event ticket. Campaign totals change only through these
 * payment hooks (inside the payment transaction) or a refund, so they can
 * never drift from confirmed payments.
 */
class Donation extends Model implements Payable
{
    use HasUlids;

    protected $fillable = ['donor_name', 'donor_mobile', 'donor_email', 'donor_pan', 'is_anonymous', 'message', 'locale'];

    protected function casts(): array
    {
        return [
            'status' => DonationStatus::class,
            'amount' => 'decimal:2',
            'is_anonymous' => 'boolean',
            'counted' => 'boolean',
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(DonationCampaign::class, 'campaign_id')->withTrashed();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** Name shown publicly: only if the Samaj allows it and the donor did not ask for anonymity. */
    public function publicName(): string
    {
        return (setting('donor_names_public') && ! $this->is_anonymous) ? $this->donor_name : __('web.donations.anonymous');
    }

    public function paymentSucceeded(Payment $payment): void
    {
        $this->payment_id = $payment->id;
        $this->status = $payment->mode === PaymentMode::Online ? DonationStatus::Successful : DonationStatus::Verified;

        if (! $this->counted) {
            $this->counted = true;
            DB::table('donation_campaigns')->where('id', $this->campaign_id)->update([
                'raised_amount' => DB::raw('raised_amount + '.(float) $this->amount),
                'donor_count' => DB::raw('donor_count + 1'),
            ]);
        }
        $this->save();
        PublicContent::flush();
    }

    public function paymentSubmitted(Payment $payment): void
    {
        $this->payment_id = $payment->id;
        $this->status = DonationStatus::Submitted;
        $this->save();
    }

    public function paymentFailed(Payment $payment): void
    {
        $this->payment_id = $payment->id;
        $this->status = $payment->mode === PaymentMode::Online ? DonationStatus::Failed : DonationStatus::Cancelled;
        $this->save();
    }

    public function paymentDescription(): string
    {
        $campaign = $this->campaign?->title_en ?? 'Samaj Donation';

        return $this->type === 'ticket'
            ? ($this->campaign?->ticket_label_en ?: 'Event Ticket')." × {$this->quantity} — {$campaign}"
            : "Donation — {$campaign}";
    }
}
