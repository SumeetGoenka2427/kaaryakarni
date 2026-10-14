<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Admin-editable bilingual content blocks for the public pages
        // (About history/values, Membership benefits/steps/FAQs, Home links …).
        // `page` + `section` say where a block appears; items are ordered.
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('page', 30);
            $table->string('section', 40);
            $table->string('label_en', 120)->nullable();
            $table->string('label_hi', 120)->nullable();
            $table->string('title_en', 200)->nullable();
            $table->string('title_hi', 200)->nullable();
            $table->text('body_en')->nullable();
            $table->text('body_hi')->nullable();
            $table->json('image')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['page', 'section', 'is_active', 'sort_order']);
        });

        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 120);
            $table->string('name_hi', 120)->nullable();
            $table->string('designation_en', 120);
            $table->string('designation_hi', 120)->nullable();
            $table->string('tier', 20)->default('general'); // leadership | general
            $table->json('photo')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();

            $table->index(['tier', 'is_active', 'sort_order']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('notice'); // event | notice
            $table->string('title_en', 200);
            $table->string('title_hi', 200)->nullable();
            $table->text('body_en')->nullable();
            $table->text('body_hi')->nullable();
            $table->date('event_date')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->char('mobile', 10);
            $table->string('email', 191)->nullable();
            $table->string('subject', 40);
            $table->text('message');
            $table->string('status', 20)->default('new'); // new | resolved
            $table->foreignId('resolved_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->char('locale', 2)->default('en');
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Homepage top banner — center carousel slides.
        Schema::create('banner_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title_en', 200);
            $table->string('title_hi', 200)->nullable();
            $table->string('subtitle_en', 300)->nullable();
            $table->string('subtitle_hi', 300)->nullable();
            $table->string('cta_label_en', 60)->nullable();
            $table->string('cta_label_hi', 60)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->json('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // Homepage top banner — left / right side panels.
        Schema::create('banner_panels', function (Blueprint $table) {
            $table->id();
            $table->string('position', 10)->unique(); // left | right
            $table->string('mode', 30);               // membership_cta | advertisement | campaign (P07)
            $table->unsignedBigInteger('campaign_id')->nullable(); // FK added with donation_campaigns (P07)
            $table->json('options')->nullable();      // which campaign metrics to show
            $table->json('ad_image')->nullable();
            $table->string('ad_url', 500)->nullable();
            $table->string('ad_label_en', 120)->nullable();
            $table->string('ad_label_hi', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_panels');
        Schema::dropIfExists('banner_slides');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('committee_members');
        Schema::dropIfExists('content_blocks');
    }
};
