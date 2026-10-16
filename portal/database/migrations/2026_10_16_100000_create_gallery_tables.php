<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gallery (P11): admin-managed albums of event photos, bilingual titles,
 * responsive WebP images on the public disk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 140)->unique();
            $table->string('title_en', 150);
            $table->string('title_hi', 150)->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_hi')->nullable();
            $table->date('event_date')->nullable();
            $table->unsignedBigInteger('cover_image_id')->nullable();
            $table->unsignedSmallInteger('images_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_sample')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('gallery_albums')->cascadeOnDelete();
            $table->json('image');
            $table->string('caption_en', 200)->nullable();
            $table->string('caption_hi', 200)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['album_id', 'sort_order']);
        });

        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->foreign('cover_image_id')->references('id')->on('gallery_images')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gallery_albums', fn (Blueprint $table) => $table->dropForeign(['cover_image_id']));
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('gallery_albums');
    }
};
