<?php

use App\Enums\MatrimonialStatus;
use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Models\AdminUser;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\FamilyMember;
use App\Models\MatrimonialProfile;
use App\Models\MatrimonialReport;
use App\Models\Member;
use App\Models\Membership;
use App\Models\SmsLog;
use App\Services\Sms\SmsProvider;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakeSmsProvider;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('private');
    $this->seed([RolesAndPermissionsSeeder::class, NotificationTemplateSeeder::class]);
    $this->app->instance(SmsProvider::class, new FakeSmsProvider);
    $this->area = Area::create(['name_en' => 'Chandpole', 'name_hi' => 'चांदपोल', 'is_active' => true]);
    $this->owner = lifetimeMember('9876543210');
    $this->viewer = lifetimeMember('9876543211');
});

function lifetimeMember(string $mobile, bool $lifetime = true): Member
{
    $m = new Member(['name' => 'Member '.$mobile, 'mobile' => $mobile]);
    $m->area_id = Area::first()->id;
    $m->status = MemberStatus::Verified;
    $m->save();
    $ms = new Membership(['member_id' => $m->id, 'fee_amount' => 1100]);
    $ms->status = $lifetime ? MembershipStatus::Active : MembershipStatus::PaymentPending;
    $ms->save();

    return $m->fresh();
}

function matProfileData(array $extra = []): array
{
    return array_merge([
        'relation' => 'son', 'name' => 'Rohit Khandelwal', 'gender' => 'male', 'dob' => '1996-03-22', 'height_cm' => 175,
        'marital_status' => 'unmarried', 'father_name' => 'Ramesh Khandelwal', 'area_id' => Area::first()->id,
        'education' => 'B.Tech', 'occupation' => 'Engineer', 'show_contact' => '1',
        'photos' => [UploadedFile::fake()->image('a.jpg', 600, 750)],
    ], $extra);
}

function approvedProfile(Member $owner, array $extra = []): MatrimonialProfile
{
    $p = new MatrimonialProfile(array_merge(['relation' => 'daughter', 'name' => 'Neha Sample', 'gender' => 'female', 'dob' => '1998-06-14',
        'marital_status' => 'unmarried', 'area_id' => Area::first()->id, 'education' => 'MBA', 'occupation' => 'Analyst', 'show_contact' => false], $extra));
    $p->member_id = $owner->id;
    $p->status = MatrimonialStatus::Approved;
    $p->save();

    return $p->fresh();
}

function matAdmin(string $role = 'super-admin'): AdminUser
{
    return AdminUser::create(['name' => 'A', 'username' => 'm'.$role, 'email' => 'm'.$role.'@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole($role)->fresh();
}

it('is never visible to guests', function () {
    $p = approvedProfile($this->owner);
    $this->get('/member/matrimonial')->assertRedirect();
    $this->get('/member/matrimonial/browse')->assertRedirect();
    $this->get('/member/matrimonial/profiles/'.$p->ulid)->assertRedirect();
    $this->get('/matrimonial-files/biodata/'.$p->ulid)->assertNotFound();
});

it('locks the module for members without an active lifetime membership', function () {
    $plain = lifetimeMember('9123456780', lifetime: false);
    $p = approvedProfile($this->owner);

    $this->actingAs($plain, 'member')->get('/member/matrimonial')->assertOk()->assertSee('Matrimonial is for Lifetime Members');
    $this->get('/member/matrimonial/browse')->assertForbidden();
    $this->get('/member/matrimonial/profiles/'.$p->ulid)->assertForbidden();
    $this->post('/member/matrimonial', matProfileData())->assertForbidden();
    expect(MatrimonialProfile::count())->toBe(1);
});

it('lets a lifetime member submit a profile that stays hidden until approved', function () {
    $this->actingAs($this->owner, 'member')->get('/member/matrimonial?new=1')->assertOk()->assertSee('Save &amp; Submit for Approval', false);
    $this->post('/member/matrimonial', matProfileData([
        'bio_data' => UploadedFile::fake()->create('bio.pdf', 200, 'application/pdf'),
    ]))->assertRedirect('/member/matrimonial');

    $p = MatrimonialProfile::sole();
    expect($p->status)->toBe(MatrimonialStatus::Pending)->and($p->photos)->toHaveCount(1)->and($p->bio_data['disk'])->toBe('private');
    Storage::disk('private')->assertExists($p->bio_data['path']);

    // Another eligible member cannot see it yet — not in browse, detail 404, files 404.
    auth('member')->logout();
    $this->actingAs($this->viewer, 'member')->get('/member/matrimonial/browse')->assertDontSee('Rohit Khandelwal')->assertSee('No matching approved profiles found');
    $this->get('/member/matrimonial/profiles/'.$p->ulid)->assertNotFound();
    $this->get('/matrimonial-files/photo/'.$p->photos->first()->id)->assertNotFound();
    $this->get('/matrimonial-files/biodata/'.$p->ulid)->assertNotFound();
});

it('requires at least one photo and caps profiles per member', function () {
    $this->actingAs($this->owner, 'member')->from('/member/matrimonial?new=1')
        ->post('/member/matrimonial', matProfileData(['photos' => []]))->assertSessionHasErrors('photos');

    foreach (range(1, MatrimonialProfile::MAX_PER_MEMBER) as $i) {
        approvedProfile($this->owner, ['name' => "Child {$i}"]);
    }
    $this->post('/member/matrimonial', matProfileData())->assertSessionHas('error');
    expect(MatrimonialProfile::count())->toBe(MatrimonialProfile::MAX_PER_MEMBER);
});

it('rejects under-18 dates of birth and non-PDF bio data', function () {
    $this->actingAs($this->owner, 'member')->post('/member/matrimonial', matProfileData([
        'dob' => now()->subYears(16)->toDateString(),
        'bio_data' => UploadedFile::fake()->create('bio.exe', 10, 'application/octet-stream'),
    ]))->assertSessionHasErrors(['dob', 'bio_data']);
});

it('shows approved profiles of other members with filters, never the viewer’s own', function () {
    approvedProfile($this->owner, ['name' => 'Neha Sample', 'gender' => 'female', 'dob' => now()->subYears(26)->toDateString()]);
    approvedProfile($this->owner, ['name' => 'Ankit Sample', 'gender' => 'male', 'dob' => now()->subYears(31)->toDateString()]);
    approvedProfile($this->viewer, ['name' => 'Viewer Own Child']);
    $hidden = approvedProfile($this->owner, ['name' => 'Hidden Child']);
    $hidden->status = MatrimonialStatus::Hidden;
    $hidden->save();

    $this->actingAs($this->viewer, 'member')->get('/member/matrimonial/browse')
        ->assertSee('Neha Sample')->assertSee('Ankit Sample')->assertDontSee('Viewer Own Child')->assertDontSee('Hidden Child');
    $this->get('/member/matrimonial/browse?gender=female')->assertSee('Neha Sample')->assertDontSee('Ankit Sample');
    $this->get('/member/matrimonial/browse?min_age=30&max_age=35')->assertSee('Ankit Sample')->assertDontSee('Neha Sample');
    $this->get('/member/matrimonial/browse?min_age=26&max_age=26')->assertSee('Neha Sample')->assertDontSee('Ankit Sample');
    $this->get('/member/matrimonial/browse?q=MBA')->assertSee('Neha Sample');
    $this->get('/hi/member/matrimonial/browse')->assertOk()->assertSee('वैवाहिक प्रोफ़ाइल देखें');
});

it('shows the contact number only when the owner allows it', function () {
    $private = approvedProfile($this->owner, ['name' => 'Private Contact', 'show_contact' => false]);
    $open = approvedProfile($this->owner, ['name' => 'Open Contact', 'show_contact' => true]);

    $this->actingAs($this->viewer, 'member')->get('/member/matrimonial/profiles/'.$private->ulid)->assertOk()
        ->assertDontSee('9876543210')->assertSee('chosen not to share');
    $this->get('/member/matrimonial/profiles/'.$open->ulid)->assertOk()->assertSee('9876543210');
});

it('does not let a member edit or delete someone else’s profile', function () {
    $p = approvedProfile($this->owner);
    $this->actingAs($this->viewer, 'member')->put('/member/matrimonial/'.$p->ulid, matProfileData())->assertNotFound();
    $this->delete('/member/matrimonial/'.$p->ulid)->assertNotFound();
    $this->get('/member/matrimonial?profile='.$p->ulid)->assertNotFound();
    expect($p->fresh()->name)->toBe('Neha Sample');
});

it('sends an approved profile back to review when the owner edits it', function () {
    $this->actingAs($this->owner, 'member')->post('/member/matrimonial', matProfileData());
    $p = MatrimonialProfile::sole();
    $p->status = MatrimonialStatus::Approved;
    $p->save();

    $this->put('/member/matrimonial/'.$p->ulid, matProfileData(['occupation' => 'Doctor', 'photos' => []]))->assertRedirect('/member/matrimonial');
    expect($p->fresh()->status)->toBe(MatrimonialStatus::Pending)->and($p->fresh()->occupation)->toBe('Doctor')->and($p->photos()->count())->toBe(1);
});

it('prefills a new profile from a family member', function () {
    $child = new FamilyMember(['relation' => 'daughter', 'name' => 'Priya Khandelwal', 'gender' => 'female', 'dob' => '2000-11-05', 'marital_status' => 'unmarried', 'education' => 'MCA']);
    $child->member_id = $this->owner->id;
    $child->save();

    $this->actingAs($this->owner, 'member')->get('/member/family')->assertSee('Create matrimonial profile');
    $this->get('/member/matrimonial?new=1&family='.$child->fresh()->ulid)->assertOk()->assertSee('Priya Khandelwal')->assertSee('2000-11-05');
});

it('lets an admin approve, reject, hide and suspend with audit log and SMS', function () {
    $this->actingAs($this->owner, 'member')->post('/member/matrimonial', matProfileData());
    $p = MatrimonialProfile::sole();
    auth('member')->logout();

    $admin = matAdmin('matrimonial-moderator');
    $this->actingAs($admin, 'admin')->get('/admin/matrimonial')->assertOk()->assertSee('Rohit Khandelwal');
    $this->get('/admin/matrimonial/'.$p->ulid)->assertOk()->assertSee('Approve');
    $this->get('/matrimonial-files/photo/'.$p->photos->first()->id.'/320')->assertOk()->assertHeader('Content-Type', 'image/webp');

    $this->patch('/admin/matrimonial/'.$p->ulid.'/approve')->assertSessionHas('success');
    expect($p->fresh()->status)->toBe(MatrimonialStatus::Approved)
        ->and(SmsLog::where('template_key', 'matrimonial_approved')->exists())->toBeTrue()
        ->and(AuditLog::where('action', 'matrimonial.approved')->exists())->toBeTrue();

    $this->patch('/admin/matrimonial/'.$p->ulid.'/hide');
    expect($p->fresh()->status)->toBe(MatrimonialStatus::Hidden);

    $this->patch('/admin/matrimonial/'.$p->ulid.'/reject', ['note' => 'Please add a clearer photo.']);
    expect($p->fresh()->status)->toBe(MatrimonialStatus::Rejected)->and($p->fresh()->admin_note)->toBe('Please add a clearer photo.')
        ->and(SmsLog::where('template_key', 'matrimonial_rejected')->exists())->toBeTrue();

    $this->patch('/admin/matrimonial/'.$p->ulid.'/approve');
    $this->patch('/admin/matrimonial/'.$p->ulid.'/suspend', ['note' => 'Reported as misuse.']);
    expect($p->fresh()->status)->toBe(MatrimonialStatus::Suspended);

    $this->delete('/admin/matrimonial/'.$p->ulid)->assertRedirect('/admin/matrimonial');
    expect(MatrimonialProfile::count())->toBe(0);
});

it('blocks admins without matrimonial permissions', function () {
    $p = approvedProfile($this->owner);
    $this->actingAs(matAdmin('content-manager'), 'admin')->get('/admin/matrimonial')->assertForbidden();
    $this->patch('/admin/matrimonial/'.$p->ulid.'/hide')->assertForbidden();
    $this->get('/matrimonial-files/biodata/'.$p->ulid)->assertNotFound();
});

it('lets members report a profile and admins resolve it', function () {
    $p = approvedProfile($this->owner);
    $this->actingAs($this->viewer, 'member')->post('/member/matrimonial/profiles/'.$p->ulid.'/report', ['reason' => 'fake', 'details' => 'Photo looks copied.'])
        ->assertSessionHas('success');
    $this->post('/member/matrimonial/profiles/'.$p->ulid.'/report', ['reason' => 'other']);
    expect(MatrimonialReport::count())->toBe(1)->and(MatrimonialReport::sole()->reason)->toBe('other');
    auth('member')->logout();

    $this->actingAs($this->owner, 'member')->post('/member/matrimonial/profiles/'.$p->ulid.'/report', ['reason' => 'fake'])->assertSessionHas('error');
    auth('member')->logout();

    $this->actingAs(matAdmin(), 'admin')->get('/admin/matrimonial?status=reports')->assertOk()->assertSee('Neha Sample');
    $this->patch('/admin/matrimonial-reports/'.MatrimonialReport::sole()->id.'/resolve')->assertSessionHas('success');
    expect(MatrimonialReport::sole()->status)->toBe('resolved');
});
