<?php

use App\Models\Announcement;
use App\Models\BannerSlide;
use App\Models\CommitteeMember;
use App\Models\ContactMessage;
use App\Models\ContentBlock;
use App\Services\Content\PublicContent;
use App\Services\Settings\SettingsService;
use Database\Seeders\WebsiteContentSeeder;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(fn () => $this->seed(WebsiteContentSeeder::class));

it('renders every public page in English and Hindi', function (string $path, string $en, string $hi) {
    $this->get($path)->assertOk()->assertSee($en);
    $this->get('/hi'.($path === '/' ? '' : $path))->assertOk()->assertSee($hi)->assertSee('<html lang="hi">', false);
})->with([
    'home' => ['/', 'A Community Rooted in Trust and Tradition', 'विश्वास और परंपरा में निहित एक समुदाय'],
    'about' => ['/about', 'Our History', 'हमारा इतिहास'],
    'committee' => ['/committee', 'Samaj Committee Members', 'समाज समिति सदस्य'],
    'membership' => ['/membership', 'Membership FAQs', 'सदस्यता संबंधी प्रश्न'],
    'contact' => ['/contact', 'Send a Message', 'संदेश भेजें'],
]);

it('shows admin-edited content on the website right away (cache refreshes on save)', function () {
    $this->get('/about')->assertSee('Our Mission');

    ContentBlock::where('page', 'about')->where('title_en', 'Our Mission')->first()
        ->update(['title_en' => 'Our Purpose', 'title_hi' => 'हमारा प्रयोजन']);

    $this->get('/about')->assertSee('Our Purpose')->assertDontSee('Our Mission');
    $this->get('/hi/about')->assertSee('हमारा प्रयोजन');
});

it('falls back to English when Hindi content is empty', function () {
    ContentBlock::where('page', 'membership')->where('section', 'faqs')->first()
        ->update(['title_en' => 'Only English question?', 'title_hi' => null]);

    $this->get('/hi/membership')->assertSee('Only English question?');
});

it('hides inactive content blocks', function () {
    ContentBlock::where('page', 'about')->where('title_en', 'Our Vision')->update(['is_active' => false]);
    PublicContent::flush();

    $this->get('/about')->assertDontSee('Our Vision');
});

it('shows the membership fee from settings with lifetime wording and no renewal', function () {
    app(SettingsService::class)->update(['membership_fee' => 7100]);

    $html = $this->get('/membership')->assertOk()->assertSee('₹7,100')->getContent();
    expect(strtolower(strip_tags($html)))->not->toContain('renew now')->not->toContain('expires on');
});

it('shows only active, scheduled carousel slides in order', function () {
    BannerSlide::create(['title_en' => 'Second slide', 'sort_order' => 2, 'is_active' => true]);
    BannerSlide::create(['title_en' => 'First slide', 'sort_order' => 1, 'is_active' => true]);
    BannerSlide::create(['title_en' => 'Hidden slide', 'sort_order' => 3, 'is_active' => false]);
    BannerSlide::create(['title_en' => 'Future slide', 'sort_order' => 4, 'is_active' => true, 'starts_at' => now()->addWeek()]);
    BannerSlide::create(['title_en' => 'Expired slide', 'sort_order' => 5, 'is_active' => true, 'ends_at' => now()->subDay()]);

    $this->get('/')->assertSeeInOrder(['First slide', 'Second slide'])
        ->assertDontSee('Hidden slide')->assertDontSee('Future slide')->assertDontSee('Expired slide');
});

it('never renders unsafe admin links', function () {
    BannerSlide::create(['title_en' => 'Bad link', 'cta_label_en' => 'Click', 'cta_url' => 'javascript:alert(1)', 'is_active' => true]);

    $this->get('/')->assertDontSee('javascript:alert', false);
});

it('renders the three-column banner with the membership panel by default', function () {
    $this->get('/')->assertSee('class="banner-grid"', false)
        ->assertSee('Become a Lifetime Member')->assertSee('Advertisement');
});

it('shows committee leadership with load-more for the general committee', function () {
    CommitteeMember::create(['name_en' => 'Shri R. Khandelwal', 'designation_en' => 'President', 'designation_hi' => 'अध्यक्ष', 'tier' => 'leadership']);
    foreach (range(1, 25) as $i) {
        CommitteeMember::create(['name_en' => "General Member {$i}", 'designation_en' => 'Member', 'tier' => 'general', 'sort_order' => $i]);
    }

    $this->get('/about')->assertSee('Shri R. Khandelwal')->assertSee('President');
    $this->get('/hi/committee')->assertSee('अध्यक्ष');
    $this->get('/committee')->assertSee('General Member 20')->assertDontSee('General Member 21')->assertSee('Load 20 More');
    $this->get('/committee?show=40')->assertSee('General Member 25');
});

it('shows only visible announcements on the homepage', function () {
    Announcement::create(['title_en' => 'Visible AGM', 'type' => 'event', 'is_active' => true]);
    Announcement::create(['title_en' => 'Hidden notice', 'type' => 'notice', 'is_active' => false]);

    $this->get('/')->assertSee('Visible AGM')->assertDontSee('Hidden notice');
});

it('accepts a contact message and validates it', function () {
    RateLimiter::clear(sha1('contact.store'));

    $this->post('/contact', ['name' => 'R', 'mobile' => '123', 'subject' => 'nope', 'message' => ''])
        ->assertSessionHasErrors(['name', 'mobile', 'subject', 'message']);

    $this->post('/hi/contact', [
        'name' => 'Ramesh Khandelwal', 'mobile' => '9876543210', 'subject' => 'membership',
        'message' => 'Please share the membership process.',
    ])->assertRedirect('/hi/contact')->assertSessionHas('success');

    $msg = ContactMessage::sole();
    expect($msg->locale)->toBe('hi')->and($msg->status)->toBe('new');
});

it('silently drops honeypot spam', function () {
    $this->post('/contact', [
        'name' => 'Spam Bot', 'mobile' => '9876543210', 'subject' => 'general', 'message' => 'Buy now', 'website' => 'http://spam',
    ])->assertSessionHas('success');

    expect(ContactMessage::count())->toBe(0);
});

it('uses office details from settings on the contact page', function () {
    app(SettingsService::class)->update(['phone_primary' => '+91 141 555 0000', 'office_hours_en' => 'Mon–Fri 9–5']);

    $this->get('/contact')->assertSee('+91 141 555 0000')->assertSee('Mon–Fri 9–5');
});
