<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name_en', 100);
            $table->string('name_hi', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // One business / professional profile per member. Only "approved" is public.
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 140)->unique();
            $table->foreignId('member_id')->unique()->constrained('members')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('business_categories')->restrictOnDelete();
            $table->foreignId('area_id')->constrained('areas')->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description');
            $table->string('address', 500)->nullable();
            $table->char('phone', 10)->nullable();
            $table->char('whatsapp', 10)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('hours', 150)->nullable();
            $table->json('logo')->nullable();
            $table->boolean('show_phone')->default(true);
            $table->boolean('show_whatsapp')->default(true);
            $table->boolean('show_email')->default(false);
            $table->string('status', 30)->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->string('admin_note', 500)->nullable(); // rejection reason / requested changes
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['status', 'category_id', 'area_id']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
        Schema::dropIfExists('business_categories');
    }
};
