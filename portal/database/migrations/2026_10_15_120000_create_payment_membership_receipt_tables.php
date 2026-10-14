<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gap-free counters (membership numbers per year, receipt numbers per financial year).
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamps();
        });

        // The single source of truth for money. Online payments become
        // successful only through server-side gateway verification.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('purpose', 20);                  // membership | donation | ticket
            $table->nullableMorphs('payable');             // Membership / Donation
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('INR');
            $table->string('mode', 10);                     // online | offline
            $table->string('provider', 20);                 // razorpay | offline
            $table->string('provider_order_id', 100)->nullable()->unique();
            $table->string('provider_payment_id', 100)->nullable()->unique();
            $table->string('offline_method', 20)->nullable();
            $table->string('offline_reference', 100)->nullable();
            $table->date('offline_paid_on')->nullable();
            $table->json('proof')->nullable();              // private disk
            $table->string('payer_name', 120)->nullable();
            $table->char('payer_mobile', 10)->nullable();
            $table->string('payer_email', 191)->nullable();
            $table->string('status', 20)->default('created');
            $table->string('failure_reason', 500)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'mode']);
            $table->index(['purpose', 'status']);
            $table->index('paid_at');
            $table->index('created_at');
        });

        // Gateway callbacks / webhooks, kept for audit and idempotency.
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('provider', 20);
            $table->string('event', 60);
            $table->string('provider_event_id', 100)->nullable()->unique();
            $table->boolean('signature_valid');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // One lifetime membership per member. No expiry / renewal columns, by design.
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->unique()->constrained('members')->cascadeOnDelete();
            $table->decimal('fee_amount', 12, 2);
            $table->string('status', 30)->default('payment_pending');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained('payments')->restrictOnDelete();
            $table->string('receipt_no', 30)->unique();
            $table->timestamp('issued_at');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sequences');
    }
};
