<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P12 notifications: Laravel's standard `notifications` table holds each
 * member's in-app inbox; `broadcasts` records admin announcements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title_en', 150);
            $table->string('title_hi', 150)->nullable();
            $table->text('message_en');
            $table->text('message_hi')->nullable();
            $table->string('type', 20)->default('announcement');
            $table->string('audience', 20); // all | lifetime | area
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->boolean('send_sms')->default(false);
            $table->unsignedInteger('recipients')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->foreignId('broadcast_id')->nullable()->constrained('broadcasts')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('broadcasts');
    }
};
