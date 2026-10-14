<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A member's family (spouse, children, parents …). Private: visible
        // only to the member and admins, never on the public website.
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('relation', 30);
            $table->string('name', 120);
            $table->string('gender', 10)->nullable();
            $table->date('dob')->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('education', 150)->nullable();
            $table->string('occupation', 150)->nullable();
            $table->char('mobile', 10)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->json('photo')->nullable(); // private disk
            $table->boolean('is_deceased')->default(false);
            $table->boolean('lives_with_member')->default(true);
            $table->string('notes', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['member_id', 'sort_order']);
        });

        // Full history of verification decisions on a member profile.
        Schema::create('member_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('action', 30); // submitted | verified | rejected | suspended | reinstated
            $table->string('reason', 500)->nullable();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['member_id', 'created_at']);
        });

        // Member profile photo moves to the private disk (JSON image data).
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
        Schema::table('members', function (Blueprint $table) {
            $table->json('photo')->nullable()->after('area_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('photo');
            $table->string('photo_path')->nullable();
        });
        Schema::dropIfExists('member_verifications');
        Schema::dropIfExists('family_members');
    }
};
