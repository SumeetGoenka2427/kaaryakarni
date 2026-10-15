<?php

use App\Enums\BusinessStatus;
use App\Enums\MemberStatus;
use App\Models\AdminUser;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\BusinessCategory;
use App\Models\BusinessProfile;
use App\Models\Member;
use App\Models\SmsLog;
use App\Services\Settings\SettingsService;
use App\Services\Sms\SmsProvider;
use Database\Seeders\BusinessCategorySeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakeSmsProvider;

beforeEach(function () {
    Storage::fake('public');
    $this->seed([RolesAndPermissionsSeeder::class, NotificationTemplateSeeder::class, BusinessCategorySeeder::class]);
    $this->app->instance(SmsProvider::class, new FakeSmsProvider);
    $this->area = Area::create(['name_en' => 'Chandpole', 'name_hi' => 'चांदपोल', 'is_active' => true]);
    $this->cat = BusinessCategory::where('slug', 'textiles')->sole();
    $this->member = newMember('9876543210', MemberStatus::Verified);
});

function newMember(string $mobile, MemberStatus $status): Member
{
    $m = new Member(['name' => 'Owner '.$mobile, 'mobile' => $mobile]);
    $m->area_id = Area::first()->id;
    $m->status = $status;
    $m->save();

    return $m->fresh();
}

function listing(array $extra = []): array
{
    return array_merge(['name' => 'Khandelwal Textiles', 'category_id' => BusinessCategory::where('slug', 'textiles')->value('id'),
        'area_id' => Area::first()->id, 'description' => 'Wholesale and retail fabrics in Chandpole market for over twenty years.',
        'phone' => '9876543210', 'show_phone' => '1', 'show_whatsapp' => '0', 'show_email' => '0', 'email' => 'secret@example.org'], $extra);
}

function businessAdmin(string $role = 'super-admin'): AdminUser
{
    return AdminUser::create(['name' => 'A', 'username' => 'a'.$role, 'email' => $role.'@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole($role)->fresh();
}

it('lets a verified member submit a listing that stays hidden until approved', function () {
    $this->actingAs($this->member, 'member')->get('/member/business')->assertOk()->assertSee('My Business Profile');
    $this->put('/member/business', listing(['logo' => UploadedFile::fake()->image('logo.jpg', 800, 600)]))->assertRedirect('/member/business');

    $b = BusinessProfile::sole();
    expect($b->status)->toBe(BusinessStatus::Pending)->and($b->logo)->not->toBeNull()->and($b->slug)->toBe('khandelwal-textiles');
    auth('member')->logout();

    $this->get('/business-directory')->assertDontSee('Khandelwal Textiles')->assertSee('No approved businesses found');
    $this->get('/business-directory/khandelwal-textiles')->assertNotFound()->assertSee('Business not found');
});

it('requires a verified member profile to list a business', function () {
    $pending = newMember('9123456780', MemberStatus::UnderVerification);
    $this->actingAs($pending, 'member')->get('/member/business')->assertSee('must be verified');
    $this->put('/member/business', listing())->assertForbidden();
    expect(BusinessProfile::count())->toBe(0);
});

it('never accepts status or featured flags from the member form', function () {
    $this->actingAs($this->member, 'member')->put('/member/business', listing(['status' => 'approved', 'is_featured' => '1']));

    $b = BusinessProfile::sole();
    expect($b->status)->toBe(BusinessStatus::Pending)->and($b->is_featured)->toBeFalse();
});

it('publishes on approval with an SMS and audit entry', function () {
    $this->actingAs($this->member, 'member')->put('/member/business', listing());
    $b = BusinessProfile::sole();

    $this->actingAs(businessAdmin(), 'admin')->patch(route('admin.businesses.approve', $b->id))->assertSessionHas('success');

    expect($b->fresh()->status)->toBe(BusinessStatus::Approved)
        ->and(SmsLog::where('template_key', 'business_approved')->exists())->toBeTrue()
        ->and(AuditLog::where('action', 'business.approved')->exists())->toBeTrue();
    $this->get('/business-directory')->assertSee('Khandelwal Textiles');
    $this->get('/hi/business-directory/khandelwal-textiles')->assertOk()->assertSee('Khandelwal Textiles')->assertSee('व्यवसाय के बारे में');
});

it('respects contact privacy on the public page', function () {
    $this->actingAs($this->member, 'member')->put('/member/business', listing());
    $b = BusinessProfile::sole();
    $this->actingAs(businessAdmin(), 'admin')->patch(route('admin.businesses.approve', $b->id));

    $this->get('/business-directory/khandelwal-textiles')->assertSee('tel:+919876543210', false)->assertDontSee('secret@example.org');
});

it('rejects and requests changes with notes the member can see, then resubmission goes to pending', function () {
    $this->actingAs($this->member, 'member')->put('/member/business', listing());
    $b = BusinessProfile::sole();
    $admin = businessAdmin();

    $this->actingAs($admin, 'admin')->patch(route('admin.businesses.changes', $b->id), ['note' => 'Please add your shop address'])->assertSessionHas('success');
    expect($b->fresh()->status)->toBe(BusinessStatus::ChangesRequested);
    $this->actingAs($this->member, 'member')->get('/member/business')->assertSee('Please add your shop address');

    $this->put('/member/business', listing(['address' => 'Shop 14, Chandpole Bazaar']));
    expect($b->fresh()->status)->toBe(BusinessStatus::Pending);

    $this->actingAs($admin, 'admin')->patch(route('admin.businesses.reject', $b->id), ['note' => ''])->assertSessionHasErrors('note');
    $this->patch(route('admin.businesses.reject', $b->id), ['note' => 'Not a Samaj-owned business'])->assertSessionHas('success');
    expect($b->fresh()->status)->toBe(BusinessStatus::Rejected);
});

it('sends an approved listing back for approval when key fields change (setting on)', function () {
    $this->actingAs($this->member, 'member')->put('/member/business', listing());
    $b = BusinessProfile::sole();
    $this->actingAs(businessAdmin(), 'admin')->patch(route('admin.businesses.approve', $b->id));

    $this->actingAs($this->member, 'member')->put('/member/business', listing(['hours' => 'Mon–Sat 10–8']));
    expect($b->fresh()->status)->toBe(BusinessStatus::Approved);

    $this->put('/member/business', listing(['name' => 'Khandelwal Textiles & Sarees']));
    expect($b->fresh()->status)->toBe(BusinessStatus::Pending);

    app(SettingsService::class)->update(['business_reapproval_on_edit' => false]);
    $b->forceFill(['status' => BusinessStatus::Approved])->save();
    $this->put('/member/business', listing(['name' => 'Khandelwal Textiles House']));
    expect($b->fresh()->status)->toBe(BusinessStatus::Approved);
});

it('features, hides and deletes listings', function () {
    $this->actingAs($this->member, 'member')->put('/member/business', listing());
    $b = BusinessProfile::sole();
    $this->actingAs(businessAdmin(), 'admin');
    $this->patch(route('admin.businesses.approve', $b->id));

    $this->patch(route('admin.businesses.feature', $b->id));
    expect($b->fresh()->is_featured)->toBeTrue();
    $this->get('/')->assertSee('Featured Businesses')->assertSee('Khandelwal Textiles');

    $this->patch(route('admin.businesses.hide', $b->id));
    expect($b->fresh()->status)->toBe(BusinessStatus::Hidden)->and($b->fresh()->is_featured)->toBeFalse();
    $this->get('/business-directory/khandelwal-textiles')->assertNotFound();

    $this->delete(route('admin.businesses.destroy', $b->id));
    expect(BusinessProfile::count())->toBe(0);
});

it('searches and filters the directory by keyword, category and area', function () {
    $other = newMember('9123456789', MemberStatus::Verified);
    $area2 = Area::create(['name_en' => 'Mansarovar', 'is_active' => true]);
    $edu = BusinessCategory::where('slug', 'education')->sole();
    $this->actingAs($this->member, 'member')->put('/member/business', listing());
    $this->actingAs($other, 'member')->put('/member/business', listing(['name' => 'Bright Future Coaching', 'category_id' => $edu->id, 'area_id' => $area2->id,
        'description' => 'Coaching for school, JEE and NEET aspirants with a Samaj scholarship batch.']));
    $this->actingAs(businessAdmin(), 'admin');
    BusinessProfile::all()->each(fn ($b) => $this->patch(route('admin.businesses.approve', $b->id)));

    $this->get('/business-directory?category=education')->assertSee('Bright Future Coaching')->assertDontSee('Khandelwal Textiles');
    $this->get('/business-directory?area='.$area2->id)->assertSee('Bright Future Coaching')->assertDontSee('Khandelwal Textiles');
    $this->get('/business-directory?q=Coaching')->assertSee('Bright Future Coaching')->assertDontSee('Khandelwal Textiles');
    $this->get('/business-directory?q=xyznothing')->assertSee('No approved businesses found');
});

it('validates listing input', function () {
    $this->actingAs($this->member, 'member')->put('/member/business', listing(['name' => '', 'description' => 'short', 'phone' => '123', 'website' => 'javascript:alert(1)']))
        ->assertSessionHasErrors(['name', 'description', 'phone', 'website']);
});

it('manages categories and keeps admins without permission out', function () {
    $this->actingAs(businessAdmin(), 'admin');
    $this->post(route('admin.categories.store'), ['name_en' => 'Chartered Accountants', 'name_hi' => 'चार्टर्ड अकाउंटेंट'])->assertSessionHas('success');
    $this->delete(route('admin.categories.destroy', $this->cat))->assertSessionHas('success'); // unused → deletable

    $this->actingAs(businessAdmin('accounts'), 'admin')->get(route('admin.businesses.index'))->assertForbidden();
});
