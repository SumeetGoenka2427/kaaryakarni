<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-editable site settings (org identity, contact, fee, toggles).
        // Read once and cached as a single array — no per-request queries.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Logos / favicon / share image managed from Admin → Branding.
        Schema::create('branding_assets', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40)->unique();
            $table->string('path');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        // Admin overrides of website UI text (lang/{en,hi}/*.php defaults).
        Schema::create('site_texts', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50);
            $table->string('key', 150);
            $table->text('value_en')->nullable();
            $table->text('value_hi')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });

        // Who did what in the admin panel, with before/after values.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('action', 80);
            $table->nullableMorphs('auditable');
            $table->string('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('site_texts');
        Schema::dropIfExists('branding_assets');
        Schema::dropIfExists('settings');
    }
};
