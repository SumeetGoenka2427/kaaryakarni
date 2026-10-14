<?php

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Models\BrandingAsset;
use App\Models\SiteText;
use App\Services\Settings\SettingsService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = AdminUser::create([
        'name' => 'Test Admin', 'username' => 'tester', 'email' => 't@example.org',
        'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('super-admin')->fresh();
    $this->actingAs($this->admin, 'admin');
});

function validSettings(array $overrides = []): array
{
    $s = SettingsService::DEFAULTS;
    foreach (['donor_names_public', 'business_reapproval_on_edit', 'show_name_beside_logo'] as $flag) {
        $s[$flag] = $s[$flag] ? '1' : '0';
    }

    return array_merge(array_map(fn ($v) => $v ?? '', $s), $overrides);
}

it('shows the settings page', function () {
    $this->get(route('admin.settings.edit'))->assertOk()
        ->assertSee('Lifetime Membership Fee')
        ->assertSee('name="org_name_hi"', false);
});

it('saves settings, shows them on the website in both languages, and audits the change', function () {
    $this->put(route('admin.settings.update'), validSettings([
        'org_name_en' => 'Khandelwal Vaishya Samaj, Jaipur',
        'org_name_hi' => 'खंडेलवाल वैश्य समाज, जयपुर',
        'phone_primary' => '+91 141 999 0000',
        'membership_fee' => '7100',
    ]))->assertRedirect()->assertSessionHas('success', 'Settings saved.');

    expect(setting('membership_fee'))->toBe(7100);

    $this->get('/')->assertSee('Khandelwal Vaishya Samaj, Jaipur')->assertSee('+91 141 999 0000');
    $this->get('/hi')->assertSee('खंडेलवाल वैश्य समाज, जयपुर');

    $log = AuditLog::where('action', 'settings.updated')->sole();
    expect($log->new_values['membership_fee'])->toBe(7100)
        ->and($log->old_values['membership_fee'])->toBe(5000);
});

it('validates settings input', function () {
    $this->put(route('admin.settings.update'), validSettings([
        'org_name_en' => '',
        'membership_fee' => '-5',
        'email' => 'not-an-email',
        'map_url' => 'javascript:alert(1)',
        'color_primary' => 'red;}body{display:none',
    ]))->assertSessionHasErrors(['org_name_en', 'membership_fee', 'email', 'map_url', 'color_primary']);
});

it('uploads a logo that then appears on every layout', function () {
    Storage::fake('public');

    $this->post(route('admin.branding.update', 'logo_header'), [
        'file' => UploadedFile::fake()->image('logo.png', 800, 200),
    ])->assertRedirect()->assertSessionHas('success');

    $asset = BrandingAsset::where('type', 'logo_header')->sole();
    Storage::disk('public')->assertExists($asset->path);
    expect($asset->width)->toBeLessThanOrEqual(480)->and($asset->path)->toEndWith('.webp');

    $this->get('/')->assertSee($asset->url(), false);
    $this->get('/hi')->assertSee($asset->url(), false);
    $this->get(route('admin.dashboard'))->assertOk();
});

it('replaces a logo with a new cache-busting version and deletes the old file', function () {
    Storage::fake('public');
    $this->post(route('admin.branding.update', 'logo_header'), ['file' => UploadedFile::fake()->image('a.png', 400, 100)]);
    $first = BrandingAsset::where('type', 'logo_header')->sole()->path;

    $this->post(route('admin.branding.update', 'logo_header'), ['file' => UploadedFile::fake()->image('b.png', 400, 100)]);
    $asset = BrandingAsset::where('type', 'logo_header')->sole();

    expect($asset->version)->toBe(2)->and($asset->url())->toContain('?v=2');
    Storage::disk('public')->assertMissing($first);
});

it('generates favicon sizes', function () {
    Storage::fake('public');
    $this->post(route('admin.branding.update', 'favicon'), ['file' => UploadedFile::fake()->image('icon.png', 512, 512)]);

    Storage::disk('public')->assertExists(['branding/favicon-v1.png', 'branding/favicon-32-v1.png', 'branding/favicon-180-v1.png']);
    $this->get('/')->assertSee('rel="apple-touch-icon"', false);
});

it('rejects non-image and SVG uploads', function (string $name, string $mime) {
    Storage::fake('public');

    $this->post(route('admin.branding.update', 'logo_header'), [
        'file' => UploadedFile::fake()->create($name, 10, $mime),
    ])->assertSessionHasErrors('file');

    expect(BrandingAsset::count())->toBe(0);
})->with([
    'php disguised as png' => ['shell.php.png', 'application/x-php'],
    'svg' => ['logo.svg', 'image/svg+xml'],
    'pdf' => ['logo.pdf', 'application/pdf'],
]);

it('returns 404 for an unknown branding type', function () {
    $this->post(route('admin.branding.update', 'evil'), ['file' => UploadedFile::fake()->image('a.png')])->assertNotFound();
});

it('removes a logo and restores the default mark', function () {
    Storage::fake('public');
    $this->post(route('admin.branding.update', 'logo_header'), ['file' => UploadedFile::fake()->image('a.png', 400, 100)]);

    $this->delete(route('admin.branding.destroy'), ['type' => 'logo_header'])->assertSessionHas('success');

    expect(BrandingAsset::count())->toBe(0);
    $this->get('/')->assertSee('<span class="brand-mark" aria-hidden="true">KS</span>', false);
});

it('lets admin override a website text in Hindi and English', function () {
    $this->put(route('admin.site-texts.update'), ['texts' => [
        'web|home.cta_heading' => ['en' => 'Join the Samaj for Life', 'hi' => 'जीवन भर के लिए समाज से जुड़ें'],
    ]])->assertSessionHas('success');

    $this->get('/')->assertSee('Join the Samaj for Life');
    $this->get('/hi')->assertSee('जीवन भर के लिए समाज से जुड़ें');
    expect(AuditLog::where('action', 'site_texts.updated')->count())->toBe(1);
});

it('stores nothing when the text equals the default, and clearing restores the default', function () {
    $this->put(route('admin.site-texts.update'), ['texts' => [
        'web|home.cta_heading' => ['en' => 'Become a Lifetime Member', 'hi' => 'आजीवन सदस्य बनें'],
    ]]);
    expect(SiteText::count())->toBe(0);

    $this->put(route('admin.site-texts.update'), ['texts' => ['web|home.cta_heading' => ['en' => 'Changed', 'hi' => '']]]);
    expect(SiteText::count())->toBe(1);

    $this->put(route('admin.site-texts.update'), ['texts' => ['web|home.cta_heading' => ['en' => '', 'hi' => '']]]);
    expect(SiteText::count())->toBe(0);
    $this->get('/')->assertSee('Become a Lifetime Member');
});

it('ignores unknown groups and keys', function () {
    $this->put(route('admin.site-texts.update'), ['texts' => [
        'validation|required' => ['en' => 'x', 'hi' => 'x'],
        'web|does.not.exist' => ['en' => 'x', 'hi' => 'x'],
    ]]);

    expect(SiteText::count())->toBe(0);
});

it('escapes admin-entered texts on the website', function () {
    $this->put(route('admin.site-texts.update'), ['texts' => [
        'web|home.cta_heading' => ['en' => '<script>alert(1)</script>', 'hi' => ''],
    ]]);

    $this->get('/')->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
});

it('lists, searches and filters site texts', function () {
    $this->get(route('admin.site-texts.index', ['section' => 'web.nav']))->assertOk()->assertSee('web.nav.home');
    $this->get(route('admin.site-texts.index', ['q' => 'Lifetime']))->assertOk()->assertSee('web.home.cta_heading');
    $this->get(route('admin.site-texts.index', ['section' => 'nope']))->assertNotFound();
});
