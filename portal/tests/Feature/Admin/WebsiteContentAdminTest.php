<?php

use App\Models\AdminUser;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BannerPanel;
use App\Models\BannerSlide;
use App\Models\CommitteeMember;
use App\Models\ContactMessage;
use App\Models\ContentBlock;
use App\Services\Content\PublicContent;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\WebsiteContentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed([RolesAndPermissionsSeeder::class, WebsiteContentSeeder::class]);
    $this->editor = AdminUser::create([
        'name' => 'Editor', 'username' => 'editor', 'email' => 'e@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('content-manager')->fresh();
    $this->actingAs($this->editor, 'admin');
});

it('opens every content admin screen', function (string $route) {
    $this->get(route($route))->assertOk();
})->with(['admin.content.index', 'admin.banners.index', 'admin.banners.create', 'admin.committee.index', 'admin.committee.create',
    'admin.announcements.index', 'admin.announcements.create', 'admin.contact-messages.index']);

it('blocks admins without content permission', function () {
    $accounts = AdminUser::create([
        'name' => 'Accounts', 'username' => 'acc', 'email' => 'a@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('accounts')->fresh();

    $this->actingAs($accounts, 'admin')->get(route('admin.banners.index'))->assertForbidden();
    $this->post(route('admin.banners.store'), ['title_en' => 'x'])->assertForbidden();
});

it('adds a value card in both languages and it appears on the About page', function () {
    $this->post(route('admin.content.store', ['about', 'values']), [
        'title_en' => 'Our Seva', 'title_hi' => 'हमारी सेवा',
        'body_en' => 'Service first.', 'body_hi' => 'सेवा पहले।', 'sort_order' => 9, 'is_active' => '1',
    ])->assertRedirect();

    $this->get('/about')->assertSee('Our Seva');
    $this->get('/hi/about')->assertSee('हमारी सेवा');
    expect(AuditLog::where('action', 'content.created')->count())->toBe(1);
});

it('refuses unknown pages/sections and deleting required single blocks', function () {
    $this->post(route('admin.content.store', ['membership', 'faqs']), ['title_en' => 'x'])->assertNotFound();

    $history = ContentBlock::where('page', 'about')->where('section', 'history')->sole();
    $this->delete(route('admin.content.destroy', $history))->assertForbidden();
});

it('rejects javascript links in content', function () {
    $this->post(route('admin.content.store', ['home', 'links']), [
        'title_en' => 'Bad', 'link_url' => 'javascript:alert(1)', 'is_active' => '1',
    ])->assertSessionHasErrors('link_url');
});

it('creates, edits, deactivates and deletes a carousel slide with an image', function () {
    $this->post(route('admin.banners.store'), [
        'title_en' => 'New Slide', 'title_hi' => 'नई स्लाइड', 'cta_label_en' => 'Join', 'cta_url' => '/membership',
        'sort_order' => 1, 'is_active' => '1', 'image' => UploadedFile::fake()->image('slide.jpg', 1600, 800),
    ])->assertRedirect();

    $slide = BannerSlide::sole();
    expect($slide->image['variants'])->not->toBeEmpty();
    Storage::disk('public')->assertExists($slide->image['path']);
    $this->get('/')->assertSee('New Slide');
    $this->get('/hi')->assertSee('नई स्लाइड');

    $this->put(route('admin.banners.update', $slide), ['title_en' => 'Edited Slide', 'cta_url' => '/about', 'sort_order' => 2, 'is_active' => '1'])->assertRedirect();
    $this->get('/')->assertSee('Edited Slide');

    $this->patch(route('admin.banners.toggle', $slide));
    $this->get('/')->assertDontSee('Edited Slide');

    $path = $slide->fresh()->image['path'];
    $this->delete(route('admin.banners.destroy', $slide))->assertRedirect(route('admin.banners.index'));
    expect(BannerSlide::count())->toBe(0);
    Storage::disk('public')->assertMissing($path);
});

it('requires an image for a new slide', function () {
    $this->post(route('admin.banners.store'), ['title_en' => 'No image'])->assertSessionHasErrors('image');
});

it('switches the right panel to an advertisement with an image and link', function () {
    $this->put(route('admin.banner-panels.update', 'right'), [
        'mode' => 'advertisement', 'ad_url' => 'https://example.org/sponsor', 'ad_label_en' => 'Our Sponsor', 'is_active' => '1',
        'ad_image' => UploadedFile::fake()->image('ad.jpg', 600, 600),
    ])->assertRedirect(route('admin.banners.index'));

    expect(BannerPanel::where('position', 'right')->sole()->ad_image)->not->toBeNull();
    $this->get('/')->assertSee('https://example.org/sponsor', false)->assertSee('rel="noopener sponsored"', false);
});

it('manages committee members with photos', function () {
    $this->post(route('admin.committee.store'), [
        'name_en' => 'Shri Test Leader', 'name_hi' => 'श्री टेस्ट', 'designation_en' => 'President', 'designation_hi' => 'अध्यक्ष',
        'tier' => 'leadership', 'is_active' => '1', 'photo' => UploadedFile::fake()->image('p.jpg', 400, 400),
    ])->assertRedirect(route('admin.committee.index'));

    $m = CommitteeMember::sole();
    expect($m->photo)->not->toBeNull();
    $this->get('/about')->assertSee('Shri Test Leader');
    $this->get('/hi/about')->assertSee('श्री टेस्ट');

    $this->delete(route('admin.committee.destroy', $m))->assertRedirect(route('admin.committee.index'));
    expect(CommitteeMember::count())->toBe(0);
    $this->get(route('admin.committee.index')); // consumes the "… removed." flash message
    $this->get('/about')->assertDontSee('Shri Test Leader');
});

it('schedules announcements', function () {
    $this->post(route('admin.announcements.store'), [
        'type' => 'event', 'title_en' => 'Future AGM', 'starts_at' => now()->addWeek()->toDateString(), 'is_active' => '1',
    ])->assertRedirect();

    $this->get('/')->assertDontSee('Future AGM');
    $this->travel(8)->days();
    PublicContent::flush();
    $this->get('/')->assertSee('Future AGM');
});

it('reads, resolves and deletes contact messages', function () {
    $msg = ContactMessage::create(['name' => 'Visitor', 'mobile' => '9876543210', 'subject' => 'general', 'message' => 'Hello Samaj']);

    $this->get(route('admin.contact-messages.show', $msg))->assertOk()->assertSee('Hello Samaj');
    $this->patch(route('admin.contact-messages.resolve', $msg))->assertRedirect();
    expect($msg->fresh()->status)->toBe('resolved')->and($msg->fresh()->resolved_by)->toBe($this->editor->id);

    $this->delete(route('admin.contact-messages.destroy', $msg));
    expect(ContactMessage::count())->toBe(0);
});

it('escapes admin content on the public site', function () {
    Announcement::create(['title_en' => '<img src=x onerror=alert(1)>', 'type' => 'notice', 'is_active' => true]);

    $this->get('/')->assertDontSee('<img src=x onerror=alert(1)>', false);
});
