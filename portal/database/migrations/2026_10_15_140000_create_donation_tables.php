<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title_en', 200);
            $table->string('title_hi', 200)->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_hi')->nullable();
            $table->json('image')->nullable();
            $table->decimal('target_amount', 14, 2)->nullable();
            // Live totals, updated in the same transaction that confirms / refunds a
            // payment (single-row reads for the homepage). Rebuild: donations:recalculate
            $table->decimal('raised_amount', 14, 2)->default(0);
            $table->unsignedInteger('donor_count')->default(0);
            $table->boolean('is_permanent')->default(false); // "Samaj Donation": cannot be deleted or deactivated
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_online')->default(true);
            $table->boolean('allow_offline')->default(true);
            $table->boolean('has_tickets')->default(false);
            $table->string('ticket_label_en', 120)->nullable();
            $table->string('ticket_label_hi', 120)->nullable();
            $table->decimal('ticket_price', 10, 2)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('campaign_id')->constrained('donation_campaigns')->restrictOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('type', 10)->default('donation'); // donation | ticket
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('amount', 12, 2);
            $table->string('donor_name', 120);
            $table->char('donor_mobile', 10);
            $table->string('donor_email', 191)->nullable();
            $table->string('donor_pan', 10)->nullable(); // for 80G receipts if the Samaj issues them
            $table->boolean('is_anonymous')->default(false);
            $table->string('message', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('counted')->default(false); // included in campaign totals
            $table->char('locale', 2)->default('en');
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('donor_mobile');
        });

        Schema::table('banner_panels', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('donation_campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('banner_panels', fn (Blueprint $table) => $table->dropForeign(['campaign_id']));
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donation_campaigns');
    }
};
