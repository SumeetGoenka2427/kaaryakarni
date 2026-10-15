<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Private matrimonial profiles (CLAUDE.md §11). Visible only to eligible
        // logged-in members after admin approval; never public.
        Schema::create('matrimonial_profiles', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->string('relation', 20);          // self | son | daughter | brother | sister | other
            $table->string('name', 120);
            $table->string('gender', 10);
            $table->date('dob');
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('father_name', 120)->nullable();
            $table->string('mother_name', 120)->nullable();
            $table->string('gotra', 60)->nullable();
            $table->string('native_place', 100)->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('education', 150)->nullable();
            $table->string('occupation', 150)->nullable();
            $table->text('about')->nullable();
            $table->json('bio_data')->nullable();    // private PDF
            $table->boolean('show_contact')->default(false);
            $table->string('status', 20)->default('pending');
            $table->string('admin_note', 500)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->boolean('is_sample')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'gender', 'dob']);
            $table->index(['status', 'area_id']);
        });

        Schema::create('matrimonial_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('matrimonial_profiles')->cascadeOnDelete();
            $table->json('image');                    // private disk
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('matrimonial_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('matrimonial_profiles')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('members')->cascadeOnDelete();
            $table->string('reason', 30);
            $table->string('details', 1000)->nullable();
            $table->string('status', 20)->default('open'); // open | resolved
            $table->foreignId('resolved_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'reporter_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matrimonial_reports');
        Schema::dropIfExists('matrimonial_photos');
        Schema::dropIfExists('matrimonial_profiles');
    }
};
