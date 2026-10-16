<?php

use App\Enums\MatrimonialStatus;
use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Models\AdminUser;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\Broadcast;
use App\Models\MatrimonialProfile;
use App\Models\Member;
use App\Models\Membership;
use App\Models\SmsLog;
use App\Services\Audit\AuditLogger;
use App\Services\Members\MemberService;
use App\Services\Notifications\Notifier;
use App\Services\Sms\SmsProvider;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Cache;
use Tests\Support\FakeSmsProvider;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, NotificationTemplateSeeder::class]);
    $this->app->instance(SmsProvider::class, new FakeSmsProvider);
    Cache::flush();
    $this->areaA = Area::create(['name_en' => 'Chandpole', 'name_hi' => 'चांदपोल', 'is_active' => true, 'sort_order' => 1]);
    $this->areaB = Area::create(['name_en' => 'Malviya Nagar', 'is_active' => true, 'sort_order' => 2]);
});

function p12Member(string $mobile, MemberStatus $status, ?Area $area = null, bool $lifetime = false, ?string $name = null): Member
{
    $m = new Member(['name' => $name ?? 'Member '.$mobile, 'mobile' => $mobile]);
    $m->area_id = ($area ?? Area::first())->id;
    $m->status = $status;
    $m->save();
    if ($lifetime) {
        $ms = new Membership(['member_id' => $m->id, 'fee_amount' => 1100]);
        $ms->status = MembershipStatus::Active;
        $ms->save();
    }

    return $m->fresh();
}

function p12Admin(string $role): AdminUser
{
    return AdminUser::create(['name' => ucfirst($role), 'username' => 'u-'.$role, 'email' => $role.'@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole($role)->fresh();
}

// ---- Dashboard ----

it('shows live dashboard counts with links to filtered lists', function () {
    p12Member('9000000001', MemberStatus::Verified, lifetime: true);
    p12Member('9000000002', MemberStatus::UnderVerification);
    p12Member('9000000003', MemberStatus::UnderVerification);

    $html = $this->actingAs(p12Admin('super-admin'), 'admin')->get('/admin')->assertOk()
        ->assertSee('Samaj Overview')->assertSee('Lifetime Members')->assertSee('Pending Approvals')->getContent();

    expect($html)->toContain('status=under_verification')
        ->and(preg_match('/Pending Member Verification<\/div>\s*<div class="value">2</', $html))->toBe(1)
        ->and(preg_match('/Total Members<\/div>\s*<div class="value">3</', $html))->toBe(1);
});

it('hides dashboard panels the admin has no permission for', function () {
    $this->actingAs(p12Admin('matrimonial-moderator'), 'admin')->get('/admin')->assertOk()
        ->assertDontSee('Recent Donations')->assertDontSee('Online Payments')->assertSee('Recent Members');
});

// ---- Reports ----

it('previews and exports reports filtered by date', function () {
    $old = p12Member('9000000011', MemberStatus::Verified, name: 'Old Member');
    $old->created_at = now()->subYear();
    $old->save();
    p12Member('9000000012', MemberStatus::Verified, $this->areaB, lifetime: true, name: 'New Member');

    $this->actingAs(p12Admin('accounts'), 'admin');
    $range = ['from' => now()->subMonth()->toDateString(), 'to' => now()->toDateString()];

    $this->get(route('admin.reports.index', $range + ['preview' => 'member']))->assertOk()
        ->assertSee('New Member')->assertDontSee('Old Member')->assertSee('1 row(s)');

    $csv = $this->get(route('admin.reports.export', ['report' => 'member', 'format' => 'csv'] + $range))->assertOk()->streamedContent();
    expect($csv)->toContain('Membership ID')->toContain('New Member')->not->toContain('Old Member');

    $area = $this->get(route('admin.reports.export', ['report' => 'area', 'format' => 'csv']))->streamedContent();
    expect($area)->toContain('"Malviya Nagar",1,1,1')->toContain('Chandpole,1,1,0');

    $this->get(route('admin.reports.export', ['report' => 'payment', 'format' => 'xlsx']))->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $pdf = $this->get(route('admin.reports.export', ['report' => 'membership', 'format' => 'pdf']))->assertOk();
    expect($pdf->headers->get('content-type'))->toBe('application/pdf');

    expect(AuditLog::where('action', 'reports.exported')->count())->toBe(4);
});

it('lets report viewers preview but not export, and rejects unknown reports', function () {
    $this->actingAs(p12Admin('membership-manager'), 'admin');
    $this->get(route('admin.reports.index', ['preview' => 'area']))->assertOk()->assertSee('needs the “Export reports” permission', false);
    $this->get(route('admin.reports.export', ['report' => 'member', 'format' => 'csv']))->assertForbidden();
    $this->get(route('admin.reports.index', ['preview' => 'secrets']))->assertNotFound();

    $this->actingAs(p12Admin('content-manager'), 'admin')->get(route('admin.reports.index'))->assertForbidden();
});

it('never puts matrimonial contact details in the approval report', function () {
    $owner = p12Member('9000000051', MemberStatus::Verified, name: 'Ramesh Owner');
    $profile = new MatrimonialProfile(['relation' => 'son', 'name' => 'Rohit Sample', 'gender' => 'male', 'dob' => '1996-03-22', 'marital_status' => 'unmarried', 'show_contact' => true]);
    $profile->member_id = $owner->id;
    $profile->status = MatrimonialStatus::Pending;
    $profile->save();
    $this->actingAs(p12Admin('super-admin'), 'admin');
    $csv = $this->get(route('admin.reports.export', ['report' => 'matrimonial', 'format' => 'csv']))->streamedContent();
    expect($csv)->toContain('Registered By')->toContain('Rohit Sample')->not->toContain('Mobile')->not->toContain('9000000051');
});

// ---- Member inbox ----

it('puts system messages in the member inbox in their language and marks them read', function () {
    $member = p12Member('9000000021', MemberStatus::UnderVerification);
    $member->locale = 'hi';
    $member->save();

    $this->actingAs(p12Admin('super-admin'), 'admin');
    app(MemberService::class)->verify($member->fresh());
    auth('admin')->logout();

    expect(SmsLog::where('template_key', 'member_verified')->count())->toBe(1)
        ->and($member->fresh()->unreadNotifications()->count())->toBe(1);

    $this->actingAs($member->fresh(), 'member')->get('/member/dashboard')->assertSee('nav-bell-count', false);
    $this->get('/member/notifications')->assertOk()->assertSee('Profile verified')->assertSee('New');
    $this->get('/hi/member/notifications')->assertOk()->assertSee('प्रोफ़ाइल सत्यापित');

    $id = $member->notifications()->first()->id;
    $this->get("/member/notifications/{$id}/open")->assertRedirect('/member/membership');
    expect($member->fresh()->unreadNotifications()->count())->toBe(0);
});

it('does not let a member open someone else’s notification', function () {
    $a = p12Member('9000000031', MemberStatus::UnderVerification);
    $b = p12Member('9000000032', MemberStatus::Verified);
    app(Notifier::class)->inbox($a, 'registration_received', ['name' => 'A']);
    $id = $a->notifications()->first()->id;

    $this->actingAs($b, 'member')->get("/member/notifications/{$id}/open")->assertNotFound();
    expect($a->fresh()->unreadNotifications()->count())->toBe(1);
    $this->get('/member/notifications')->assertSee('No notifications yet');
});

// ---- Announcements ----

it('sends announcements only to the chosen audience, with optional SMS', function () {
    $lifetimeA = p12Member('9000000041', MemberStatus::Verified, $this->areaA, lifetime: true);
    $plainB = p12Member('9000000042', MemberStatus::Verified, $this->areaB);
    $suspended = p12Member('9000000043', MemberStatus::Suspended, $this->areaA, lifetime: true);

    $this->actingAs(p12Admin('membership-manager'), 'admin');
    $this->post(route('admin.notifications.store'), [
        'title_en' => 'AGM on Sunday', 'title_hi' => 'रविवार को वार्षिक सभा', 'message_en' => 'All lifetime members are invited.',
        'type' => 'event', 'audience' => 'lifetime', 'send_sms' => '1',
    ])->assertRedirect(route('admin.notifications.index'))->assertSessionHas('success', 'Announcement sent to 1 member(s) with SMS.');

    expect($lifetimeA->notifications()->count())->toBe(1)
        ->and($plainB->notifications()->count())->toBe(0)
        ->and($suspended->notifications()->count())->toBe(0)
        ->and(SmsLog::where('template_key', 'announcement')->count())->toBe(1);

    $this->post(route('admin.notifications.store'), ['title_en' => 'Area meet', 'message_en' => 'Malviya Nagar members meet.', 'type' => 'announcement', 'audience' => 'area', 'area_id' => $this->areaB->id]);
    expect($plainB->notifications()->count())->toBe(1)->and($lifetimeA->notifications()->count())->toBe(1)
        ->and(SmsLog::where('template_key', 'announcement')->count())->toBe(1);

    $this->post(route('admin.notifications.store'), ['title_en' => 'x', 'message_en' => 'y', 'type' => 'announcement', 'audience' => 'area'])->assertSessionHasErrors('area_id');

    auth('admin')->logout();
    $this->actingAs($lifetimeA->fresh(), 'member')->get('/hi/member/notifications')->assertSee('रविवार को वार्षिक सभा');
    auth('member')->logout();

    $this->actingAs(p12Admin('super-admin'), 'admin')->get(route('admin.notifications.index'))->assertOk()->assertSee('AGM on Sunday')->assertSee('Lifetime members only');
    $this->delete(route('admin.notifications.destroy', Broadcast::where('title_en', 'AGM on Sunday')->first()));
    expect($lifetimeA->notifications()->count())->toBe(0);
});

it('blocks announcements for admins without permission', function () {
    $this->actingAs(p12Admin('content-manager'), 'admin');
    $this->get(route('admin.notifications.index'))->assertForbidden();
    $this->post(route('admin.notifications.store'), ['title_en' => 'x', 'message_en' => 'y', 'type' => 'announcement', 'audience' => 'all'])->assertForbidden();
    expect(Broadcast::count())->toBe(0);
});

// ---- Audit log ----

it('lists and filters the audit log with change details, for permitted admins only', function () {
    $admin = p12Admin('super-admin');
    $this->actingAs($admin, 'admin');
    app(AuditLogger::class)->record('areas.updated', $this->areaA, 'Renamed area Chandpole', ['name_en' => 'Chandpol'], ['name_en' => 'Chandpole']);
    app(AuditLogger::class)->record('gallery.album_created', null, 'Created gallery album Holi');

    $this->get(route('admin.audit-logs.index'))->assertOk()->assertSee('Renamed area Chandpole')->assertSee('Created gallery album Holi')->assertSee('Chandpol');
    $this->get(route('admin.audit-logs.index', ['module' => 'gallery']))->assertSee('Created gallery album Holi')->assertDontSee('Renamed area Chandpole');
    $this->get(route('admin.audit-logs.index', ['q' => 'Renamed']))->assertSee('Renamed area Chandpole')->assertDontSee('Created gallery album Holi');

    $this->actingAs(p12Admin('accounts'), 'admin')->get(route('admin.audit-logs.index'))->assertForbidden();
});
