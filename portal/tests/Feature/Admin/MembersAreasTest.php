<?php

use App\Enums\MemberStatus;
use App\Models\AdminUser;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\FamilyMember;
use App\Models\Member;
use App\Models\MemberVerification;
use App\Models\SmsLog;
use App\Services\Sms\SmsProvider;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Tests\Support\FakeSmsProvider;

beforeEach(function () {
    $this->seed([RolesAndPermissionsSeeder::class, NotificationTemplateSeeder::class]);
    $this->app->instance(SmsProvider::class, new FakeSmsProvider);
    $this->admin = AdminUser::create(['name' => 'MM', 'username' => 'mm', 'email' => 'm@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true])
        ->assignRole('membership-manager')->fresh();
    $this->actingAs($this->admin, 'admin');
    $this->area = Area::create(['name_en' => 'Chandpole', 'is_active' => true, 'sort_order' => 1]);
});

function pendingMember(array $attrs = []): Member
{
    $m = new Member(array_merge(['name' => 'Sunita Agarwal', 'mobile' => '9876543211'], $attrs));
    $m->area_id = Area::first()->id;
    $m->status = MemberStatus::UnderVerification;
    $m->profile_submitted_at = now();
    $m->save();

    return $m->fresh();
}

it('lists, searches and filters members', function () {
    pendingMember();
    pendingMember(['name' => 'Vikas Mittal', 'mobile' => '9876543212']);

    $this->get(route('admin.members.index'))->assertOk()->assertSee('Sunita Agarwal')->assertSee('Vikas Mittal');
    $this->get(route('admin.members.index', ['q' => 'Vikas']))->assertSee('Vikas Mittal')->assertDontSee('Sunita Agarwal');
    $this->get(route('admin.members.index', ['status' => 'verified']))->assertSee('No members found');
});

it('verifies a member with history, audit log and SMS', function () {
    $m = pendingMember();

    $this->patch(route('admin.members.verify', $m))->assertSessionHas('success');

    $m->refresh();
    expect($m->status)->toBe(MemberStatus::Verified)->and($m->verified_at)->not->toBeNull()
        ->and(MemberVerification::where('member_id', $m->id)->where('action', 'verified')->exists())->toBeTrue()
        ->and(AuditLog::where('action', 'member.verified')->exists())->toBeTrue()
        ->and(SmsLog::where('template_key', 'member_verified')->exists())->toBeTrue();
});

it('requires a reason to reject or suspend', function () {
    $m = pendingMember();

    $this->patch(route('admin.members.reject', $m), ['reason' => ''])->assertSessionHasErrors('reason');
    $this->patch(route('admin.members.reject', $m), ['reason' => 'Details do not match records'])->assertSessionHas('success');
    expect($m->fresh()->status)->toBe(MemberStatus::Rejected)->and($m->fresh()->rejection_reason)->toBe('Details do not match records');
});

it('suspends and reinstates, returning to the previous state', function () {
    $m = pendingMember();
    $this->patch(route('admin.members.verify', $m));
    $this->patch(route('admin.members.suspend', $m), ['reason' => 'Misuse reported']);
    expect($m->fresh()->status)->toBe(MemberStatus::Suspended);

    $this->patch(route('admin.members.reinstate', $m));
    expect($m->fresh()->status)->toBe(MemberStatus::Verified);
});

it('refuses invalid status transitions', function () {
    $m = pendingMember();
    $this->patch(route('admin.members.reinstate', $m))->assertSessionHas('error');
    expect($m->fresh()->status)->toBe(MemberStatus::UnderVerification);
});

it('registers a member manually and edits details including mobile', function () {
    $this->post(route('admin.members.store'), ['name' => 'Offline Member', 'mobile' => '9000000001', 'area_id' => $this->area->id])->assertRedirect();
    $m = Member::where('mobile', '9000000001')->sole();
    expect($m->status)->toBe(MemberStatus::UnderVerification);

    $this->put(route('admin.members.update', $m), ['name' => 'Offline Member', 'mobile' => '9000000002', 'area_id' => $this->area->id])->assertRedirect();
    expect($m->fresh()->mobile)->toBe('9000000002');

    $this->post(route('admin.members.store'), ['name' => 'Dup', 'mobile' => '9000000002', 'area_id' => $this->area->id])->assertSessionHasErrors('mobile');
});

it("manages a member's family from admin", function () {
    $m = pendingMember();
    $this->post(route('admin.members.family.store', $m), ['relation' => 'daughter', 'name' => 'Priya Agarwal'])->assertRedirect();
    $p = FamilyMember::sole();

    $this->get(route('admin.members.show', [$m, 'tab' => 'family']))->assertSee('Priya Agarwal');
    $this->put(route('admin.members.family.update', [$m, $p->ulid]), ['relation' => 'daughter', 'name' => 'Priya A'])->assertRedirect();
    expect($p->fresh()->name)->toBe('Priya A');
});

it('exports members as CSV', function () {
    pendingMember();
    $response = $this->get(route('admin.members.export'));

    $response->assertOk();
    expect($response->streamedContent())->toContain('Sunita Agarwal')->toContain('9876543211');
});

it('manages Jaipur areas: add, rename, reorder, deactivate, delete only unused', function () {
    $this->post(route('admin.areas.store'), ['name_en' => 'Malviya Nagar', 'name_hi' => 'मालवीय नगर'])->assertSessionHas('success');
    $this->post(route('admin.areas.store'), ['name_en' => 'Malviya Nagar'])->assertSessionHasErrors('name_en');
    $new = Area::where('name_en', 'Malviya Nagar')->sole();

    $this->patch(route('admin.areas.move', $new), ['direction' => 'up']);
    expect(Area::orderBy('sort_order')->first()->id)->toBe($new->id);

    $this->patch(route('admin.areas.toggle', $new));
    expect($new->fresh()->is_active)->toBeFalse()->and(Area::activeList()->pluck('id'))->not->toContain($new->id);

    pendingMember(); // uses Chandpole
    $this->delete(route('admin.areas.destroy', $this->area))->assertSessionHas('error');
    $this->delete(route('admin.areas.destroy', $new))->assertSessionHas('success');
    expect(Area::count())->toBe(1);
});

it('keeps content admins out of members and areas', function () {
    $editor = AdminUser::create(['name' => 'Ed', 'username' => 'ed', 'email' => 'ed@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true])
        ->assignRole('content-manager')->fresh();
    $m = pendingMember();

    $this->actingAs($editor, 'admin');
    $this->get(route('admin.members.index'))->assertForbidden();
    $this->patch(route('admin.members.verify', $m))->assertForbidden();
    $this->get(route('admin.areas.index'))->assertForbidden();
});
