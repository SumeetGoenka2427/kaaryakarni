<?php

use App\Enums\MemberStatus;
use App\Models\Area;
use App\Models\FamilyMember;
use App\Models\Member;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    $this->area = Area::create(['name_en' => 'Chandpole', 'name_hi' => 'चांदपोल', 'is_active' => true]);
    $this->member = tap(new Member(['name' => 'Ramesh Khandelwal', 'mobile' => '9876543210', 'area_id' => $this->area->id]), function ($m) {
        $m->status = MemberStatus::Verified;
        $m->profile_submitted_at = now();
        $m->save();
    })->fresh();
    $this->actingAs($this->member, 'member');
});

function otherMemberWithFamily(): array
{
    $other = new Member(['name' => 'Other Person', 'mobile' => '9123456780']);
    $other->status = MemberStatus::Verified;
    $other->save();
    $person = new FamilyMember(['relation' => 'son', 'name' => 'Secret Son']);
    $person->member_id = $other->id;
    $person->save();

    return [$other->fresh(), $person->fresh()];
}

it('shows and updates the profile, but never the mobile number or status', function () {
    $this->get('/member/profile')->assertOk()->assertSee('My Profile')->assertSee('9876543210')
        ->assertDontSee('name="city"', false)->assertDontSee('name="state"', false);
    $this->get('/hi/member/profile')->assertOk()->assertSee('मेरी प्रोफ़ाइल');

    $this->put('/member/profile', [
        'name' => 'Ramesh K Khandelwal', 'area_id' => $this->area->id, 'gender' => 'male',
        'mobile' => '9999999999', 'status' => 'suspended',
    ])->assertRedirect('/member/profile')->assertSessionHas('success');

    $m = $this->member->fresh();
    expect($m->name)->toBe('Ramesh K Khandelwal')->and($m->mobile)->toBe('9876543210')->and($m->status)->toBe(MemberStatus::Verified);
});

it('stores the profile photo privately and serves it only to the owner or an admin', function () {
    $this->put('/member/profile', ['name' => 'Ramesh Khandelwal', 'area_id' => $this->area->id,
        'photo' => UploadedFile::fake()->image('me.jpg', 600, 600)]);

    $photo = $this->member->fresh()->photo;
    expect($photo['disk'])->toBe('private');
    Storage::disk('private')->assertExists($photo['path']);

    $this->get(route('photos.member', $this->member))->assertOk()->assertHeader('Content-Type', 'image/webp');

    [$other] = otherMemberWithFamily();
    $this->actingAs($other, 'member')->get(route('photos.member', $this->member))->assertNotFound();
    auth('member')->logout();
    $this->get(route('photos.member', $this->member))->assertNotFound();
});

it('adds, edits and removes family members', function () {
    $this->get('/member/family')->assertOk()->assertSee('No family members added yet');

    $this->post('/member/family', [
        'relation' => 'spouse', 'name' => 'Sunita Khandelwal', 'gender' => 'female', 'dob' => '1982-05-10',
        'marital_status' => 'married', 'occupation' => 'Teacher', 'blood_group' => 'B+', 'lives_with_member' => '1',
    ])->assertRedirect('/member/family');

    $person = FamilyMember::sole();
    expect($person->member_id)->toBe($this->member->id);
    $this->get('/member/family')->assertSee('Sunita Khandelwal')->assertSee('Teacher');
    $this->get('/hi/member/family')->assertSee('पति / पत्नी');

    $this->put("/member/family/{$person->ulid}", ['relation' => 'spouse', 'name' => 'Sunita R Khandelwal'])->assertRedirect('/member/family');
    expect($person->fresh()->name)->toBe('Sunita R Khandelwal');

    $this->delete("/member/family/{$person->ulid}")->assertRedirect('/member/family');
    expect(FamilyMember::count())->toBe(0);
});

it('allows only one father and one mother', function () {
    $this->post('/member/family', ['relation' => 'father', 'name' => 'Shri Mohan Lal']);
    $this->post('/member/family', ['relation' => 'father', 'name' => 'Another Father'])->assertSessionHasErrors('relation');

    expect(FamilyMember::count())->toBe(1);
});

it('validates family input', function () {
    $this->post('/member/family', ['relation' => 'cousin-ish', 'name' => '', 'mobile' => '123', 'dob' => now()->addDay()->toDateString(), 'blood_group' => 'Z+'])
        ->assertSessionHasErrors(['relation', 'name', 'mobile', 'dob', 'blood_group']);
});

it("never lets a member read or change another member's family", function () {
    [, $secret] = otherMemberWithFamily();

    $this->get("/member/family/{$secret->ulid}/edit")->assertNotFound();
    $this->put("/member/family/{$secret->ulid}", ['relation' => 'son', 'name' => 'Hacked'])->assertNotFound();
    $this->delete("/member/family/{$secret->ulid}")->assertNotFound();
    $this->get('/member/family')->assertDontSee('Secret Son');

    expect($secret->fresh()->name)->toBe('Secret Son');
});

it('never shows family details on public pages', function () {
    $this->post('/member/family', ['relation' => 'son', 'name' => 'Private Kid Name']);
    auth('member')->logout();

    foreach (['/', '/about', '/committee', '/membership', '/contact'] as $url) {
        $this->get($url)->assertDontSee('Private Kid Name');
    }
});

it('shows quick actions and family count on the dashboard', function () {
    $this->post('/member/family', ['relation' => 'son', 'name' => 'Rohit Khandelwal']);

    $this->get('/member/dashboard')->assertOk()->assertSee('Edit Profile')->assertSee('My Family')->assertSee('1 family member(s) added');
});
