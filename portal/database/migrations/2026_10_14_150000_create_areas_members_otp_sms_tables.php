<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-managed Jaipur areas / localities (replaces city/state fields).
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 100);
            $table->string('name_hi', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_sample')->default(false); // demo data until the client's list arrives
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // Samaj members (website accounts, mobile OTP login, `member` guard).
        // Deliberately no city/state and no membership expiry columns.
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('membership_no', 20)->nullable()->unique();
            $table->string('name', 120)->nullable();
            $table->string('father_or_husband_name', 120)->nullable();
            $table->char('mobile', 10)->unique();
            $table->string('email', 191)->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 10)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->restrictOnDelete();
            $table->string('photo_path')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->char('locale', 2)->default('en');
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('profile_submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });

        // One-time passwords. Only a hash of the code is stored.
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->char('mobile', 10);
            $table->string('purpose', 20);
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mobile', 'purpose', 'created_at']);
        });

        // SMS / notification text per language. DLT template id is required by Indian telecom rules.
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('description', 191)->nullable();
            $table->text('sms_en');
            $table->text('sms_hi')->nullable();
            $table->string('dlt_template_id', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Delivery log. Never stores the message text (it may contain an OTP).
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->char('mobile', 10);
            $table->string('template_key', 60);
            $table->string('provider', 30);
            $table->string('status', 20)->default('queued');
            $table->string('provider_message_id', 100)->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['mobile', 'template_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('members');
        Schema::dropIfExists('areas');
    }
};
