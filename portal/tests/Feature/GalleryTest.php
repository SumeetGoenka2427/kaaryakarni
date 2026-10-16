<?php

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = AdminUser::create(['name' => 'A', 'username' => 'gadmin', 'email' => 'g@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])
        ->assignRole('content-manager')->fresh();
});

function galleryPhotos(int $n): array
{
    return array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg", 1200, 900), range(1, $n));
}

function albumWithPhotos(int $photos = 3, array $extra = []): GalleryAlbum
{
    test()->actingAs(test()->admin, 'admin')->post(route('admin.gallery.store'), array_merge([
        'title_en' => 'Diwali Milan 2026', 'title_hi' => 'दीवाली मिलन 2026', 'description_en' => 'Annual gathering.', 'is_active' => '1',
    ], $extra));
    $album = GalleryAlbum::latest('id')->first();
    if ($photos) {
        test()->post(route('admin.gallery.upload', $album->id), ['photos' => galleryPhotos($photos)]);
    }
    auth('admin')->logout();

    return $album->fresh();
}

it('lets an admin create an album, upload photos and show it publicly in both languages', function () {
    $album = albumWithPhotos(3);

    expect($album->slug)->toBe('diwali-milan-2026')->and($album->images_count)->toBe(3)
        ->and($album->cover_image_id)->toBe($album->images()->first()->id)
        ->and(AuditLog::where('action', 'gallery.images_added')->exists())->toBeTrue();
    Storage::disk('public')->assertExists($album->images()->first()->image['path']);

    $this->get('/gallery')->assertOk()->assertSee('Diwali Milan 2026')->assertSee('Annual gathering.')->assertSee('3 photos');
    $this->get('/hi/gallery')->assertOk()->assertSee('दीवाली मिलन 2026')->assertSee('3 फ़ोटो');
    $this->get('/gallery/diwali-milan-2026')->assertOk()->assertSee('data-gallery-tile', false);
    $this->get('/')->assertSee('Moments From the Samaj')->assertSee('Diwali Milan 2026');
});

it('hides inactive and empty albums from the public site', function () {
    albumWithPhotos(0, ['title_en' => 'Empty Album']);
    $inactive = albumWithPhotos(2, ['title_en' => 'Hidden Album']);
    $this->actingAs($this->admin, 'admin')->patch(route('admin.gallery.toggle', $inactive->id));
    auth('admin')->logout();

    expect($inactive->fresh()->is_active)->toBeFalse();
    $this->get('/about'); // consume the admin flash message
    $this->get('/gallery')->assertDontSee('Empty Album')->assertDontSee('Hidden Album')->assertSee('No gallery albums are currently available');
    $this->get('/gallery/empty-album')->assertNotFound();
    $this->get('/gallery/hidden-album')->assertNotFound();
    $this->get('/')->assertDontSee('Hidden Album');
});

it('reorders photos, sets the cover and keeps counts right on delete', function () {
    $album = albumWithPhotos(3);
    [$a, $b, $c] = $album->images()->get()->all();
    $this->actingAs($this->admin, 'admin');

    $this->patch(route('admin.gallery.images.move', $c->id), ['direction' => 'up']);
    expect($album->images()->pluck('id')->all())->toBe([$a->id, $c->id, $b->id]);
    $this->patch(route('admin.gallery.images.move', $a->id), ['direction' => 'up']); // already first: no change
    expect($album->images()->pluck('id')->all())->toBe([$a->id, $c->id, $b->id]);

    $this->patch(route('admin.gallery.images.cover', $b->id))->assertSessionHas('success');
    expect($album->fresh()->cover_image_id)->toBe($b->id);

    $this->delete(route('admin.gallery.images.destroy', $b->id));
    $album->refresh();
    expect($album->images_count)->toBe(2)->and($album->cover_image_id)->toBe($a->id);
    Storage::disk('public')->assertMissing($b->image['path']);

    $this->put(route('admin.gallery.images.update', $a->id), ['caption_en' => 'Lamp lighting', 'caption_hi' => 'दीप प्रज्ज्वलन']);
    auth('admin')->logout();
    $this->get('/gallery/diwali-milan-2026')->assertSee('Lamp lighting');
    $this->get('/hi/gallery/diwali-milan-2026')->assertSee('दीप प्रज्ज्वलन');
});

it('deletes an album with all its photo files', function () {
    $album = albumWithPhotos(2);
    $paths = $album->images()->get()->map(fn ($i) => $i->image['path']);

    $this->actingAs($this->admin, 'admin')->delete(route('admin.gallery.destroy', $album->id))->assertRedirect(route('admin.gallery.index'));
    expect(GalleryAlbum::count())->toBe(0)->and(GalleryImage::count())->toBe(0);
    $paths->each(fn ($p) => Storage::disk('public')->assertMissing($p));
});

it('validates uploads and album fields', function () {
    $album = albumWithPhotos(0);
    $this->actingAs($this->admin, 'admin');

    $this->post(route('admin.gallery.upload', $album->id), ['photos' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]])->assertSessionHasErrors('photos.0');
    $this->post(route('admin.gallery.upload', $album->id), ['photos' => galleryPhotos(GalleryAlbum::MAX_UPLOAD + 1)])->assertSessionHasErrors('photos');
    $this->post(route('admin.gallery.upload', $album->id), [])->assertSessionHasErrors('photos');
    $this->post(route('admin.gallery.store'), ['title_en' => ''])->assertSessionHasErrors('title_en');
    expect($album->images()->count())->toBe(0);
});

it('gives each album a unique slug', function () {
    albumWithPhotos(1);
    $second = albumWithPhotos(1);
    expect($second->slug)->toBe('diwali-milan-2026-2');
});

it('blocks gallery management for guests and admins without content permission', function () {
    $album = albumWithPhotos(1);
    $this->get(route('admin.gallery.index'))->assertRedirect(route('admin.login'));

    $accounts = AdminUser::create(['name' => 'B', 'username' => 'acc', 'email' => 'acc@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('accounts')->fresh();
    $this->actingAs($accounts, 'admin')->get(route('admin.gallery.index'))->assertForbidden();
    $this->delete(route('admin.gallery.destroy', $album->id))->assertForbidden();
    expect(GalleryAlbum::count())->toBe(1);
});
