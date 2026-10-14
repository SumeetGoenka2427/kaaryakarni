<?php

use App\Enums\MemberStatus;
use App\Models\Area;
use App\Models\Member;
use App\Models\OtpCode;
use App\Models\SmsLog;
use App\Services\Sms\SmsProvider;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\FakeSmsProvider;

beforeEach(function () {
    $this->seed(NotificationTemplateSeeder::class);
    $this->sms = new FakeSmsProvider;
    $this->app->instance(SmsProvider::class, $this->sms);
    RateLimiter::clear('otp-mobile:9876543210');
    RateLimiter::clear('otp-ip:127.0.0.1');
    $this->area = Area::create(['name_en' => 'Chandpole', 'name_hi' => 'चांदपोल', 'is_active' => true]);
});

function makeMember(array $attrs = []): Member
{
    $status = $attrs['status'] ?? MemberStatus::Verified;
    unset($attrs['status']);
    $m = new Member(array_merge(['name' => 'Ramesh Khandelwal', 'mobile' => '9876543210'], $attrs));
    $m->status = $status;
    $m->profile_submitted_at = now();
    $m->save();

    return $m->fresh();
}

function requestOtp($test, string $mobile = '9876543210', string $purpose = 'login')
{
    return $test->post(route('otp.send'), ['mobile' => $mobile, 'purpose' => $purpose]);
}

it('shows the login and register pages in both languages', function () {
    $this->get('/login')->assertOk()->assertSee('Member Login')->assertSee('name="mobile"', false);
    $this->get('/hi/login')->assertOk()->assertSee('सदस्य लॉगिन');
    $this->get('/register')->assertOk()->assertSee('Become a Lifetime Member');
});

it('rejects an invalid mobile number', function (string $mobile) {
    requestOtp($this, $mobile)->assertSessionHasErrors('mobile');
    expect($this->sms->sent)->toBeEmpty();
})->with(['12345', '5876543210', 'abcdefghij', '98765432101']);

it('sends an OTP, stores only its hash and never logs the code', function () {
    requestOtp($this)->assertRedirect(route('otp.verify'));

    $code = $this->sms->lastOtp();
    expect($code)->toMatch('/^\d{6}$/');

    $row = OtpCode::sole();
    expect($row->code_hash)->not->toBe($code)
        ->and(Hash::check($code, $row->code_hash))->toBeTrue()
        ->and(SmsLog::sole()->toArray())->not->toContain($code);
});

it('logs in an existing member with a valid OTP', function () {
    $member = makeMember();
    requestOtp($this);

    $this->post(route('otp.check'), ['otp' => $this->sms->lastOtp()])->assertRedirect(route('member.dashboard'));

    $this->assertAuthenticatedAs($member, 'member');
    $this->get(route('member.dashboard'))->assertOk()->assertSee('Welcome, Ramesh');
});

it('rejects a wrong OTP and counts attempts', function () {
    makeMember();
    requestOtp($this);

    $this->post(route('otp.check'), ['otp' => '000000'])->assertSessionHasErrors('otp');
    $this->assertGuest('member');
    expect(OtpCode::sole()->attempts)->toBe(1);
});

it('blocks verification after the maximum attempts, even with the right code', function () {
    makeMember();
    requestOtp($this);
    $code = $this->sms->lastOtp();
    $wrong = $code === '111111' ? '222222' : '111111';

    foreach (range(1, config('portal.otp.max_attempts')) as $i) {
        $this->post(route('otp.check'), ['otp' => $wrong]);
    }

    $this->post(route('otp.check'), ['otp' => $code])->assertSessionHasErrors('otp');
    $this->assertGuest('member');
});

it('rejects an expired OTP', function () {
    makeMember();
    requestOtp($this);
    OtpCode::query()->update(['expires_at' => now()->subSecond()]);

    $this->post(route('otp.check'), ['otp' => $this->sms->lastOtp()])->assertSessionHasErrors('otp');
    $this->assertGuest('member');
});

it('makes an OTP single-use', function () {
    makeMember();
    requestOtp($this);
    $code = $this->sms->lastOtp();
    $this->post(route('otp.check'), ['otp' => $code]);
    $this->post(route('logout'));

    $this->withSession(['otp' => ['mobile' => '9876543210', 'purpose' => 'login']])
        ->post(route('otp.check'), ['otp' => $code])->assertSessionHasErrors('otp');
});

it('enforces the resend cooldown and invalidates the previous code on resend', function () {
    requestOtp($this);
    $first = $this->sms->lastOtp();

    $this->post(route('otp.resend'))->assertSessionHas('warning');
    expect($this->sms->sent)->toHaveCount(1);

    $this->travel(config('portal.otp.resend_cooldown') + 1)->seconds();
    $this->post(route('otp.resend'))->assertSessionHas('status');
    expect($this->sms->sent)->toHaveCount(2);

    if ($first !== $this->sms->lastOtp()) {
        $this->post(route('otp.check'), ['otp' => $first])->assertSessionHasErrors('otp');
    }
});

it('rate limits OTP requests per mobile number', function () {
    foreach (range(1, config('portal.otp.max_per_mobile_per_hour')) as $i) {
        requestOtp($this);
        $this->travel(config('portal.otp.resend_cooldown') + 1)->seconds();
    }

    requestOtp($this)->assertSessionHasErrors('mobile');
    expect($this->sms->sent)->toHaveCount(config('portal.otp.max_per_mobile_per_hour'));
});

it('shows an error and keeps no code when the SMS gateway fails', function () {
    $this->sms->fail = true;

    requestOtp($this)->assertSessionHasErrors('mobile');
    expect(OtpCode::count())->toBe(0);
});

it('does not reveal whether a number is registered', function () {
    $unknown = requestOtp($this, '9123456780');
    RateLimiter::clear('otp-ip:127.0.0.1');
    makeMember();
    $known = requestOtp($this);

    expect($unknown->headers->get('Location'))->toBe($known->headers->get('Location'))
        ->and(session('status'))->toBe(__('web.auth.otp_sent'));
});

it('registers a new member: mobile → OTP → details with a Jaipur area', function () {
    requestOtp($this, '9123456780', 'register');
    $this->post(route('otp.check'), ['otp' => $this->sms->lastOtp()])->assertRedirect(route('register.details'));

    $this->get(route('register.details'))->assertOk()->assertSee('Chandpole')
        ->assertDontSee('name="city"', false)->assertDontSee('name="state"', false);

    $this->post(route('register.store'), [
        'name' => 'Sunita Agarwal', 'gender' => 'female', 'area_id' => $this->area->id,
        'address' => 'Malviya Nagar', 'consent' => '1',
    ])->assertRedirect(route('member.dashboard'));

    $member = Member::where('mobile', '9123456780')->sole();
    expect($member->status)->toBe(MemberStatus::UnderVerification)
        ->and($member->area_id)->toBe($this->area->id)
        ->and($member->consented_at)->not->toBeNull();
    $this->assertAuthenticatedAs($member, 'member');
});

it('takes the mobile number from the verified session, never from the form', function () {
    $this->withSession(['registration' => ['mobile' => '9123456780', 'verified_at' => now()->timestamp]])
        ->post(route('register.store'), [
            'name' => 'Sunita Agarwal', 'area_id' => $this->area->id, 'consent' => '1',
            'mobile' => '9999999999', 'status' => 'verified',
        ]);

    $member = Member::sole();
    expect($member->mobile)->toBe('9123456780')->and($member->status)->toBe(MemberStatus::UnderVerification);
});

it('refuses registration details without a verified mobile', function () {
    $this->get(route('register.details'))->assertRedirect(route('register'));
    $this->post(route('register.store'), ['name' => 'X Y Z', 'area_id' => $this->area->id, 'consent' => '1'])->assertRedirect(route('register'));
    expect(Member::count())->toBe(0);
});

it('only accepts active areas', function () {
    $inactive = Area::create(['name_en' => 'Old Area', 'is_active' => false]);

    $this->withSession(['registration' => ['mobile' => '9123456780', 'verified_at' => now()->timestamp]])
        ->post(route('register.store'), ['name' => 'Sunita Agarwal', 'area_id' => $inactive->id, 'consent' => '1'])
        ->assertSessionHasErrors('area_id');
});

it('keeps suspended members out', function () {
    makeMember(['status' => MemberStatus::Suspended]);
    requestOtp($this);

    $this->post(route('otp.check'), ['otp' => $this->sms->lastOtp()])->assertRedirect(route('login'));
    $this->assertGuest('member');
});

it('signs out a member suspended while logged in', function () {
    $member = makeMember();
    $this->actingAs($member, 'member');
    $member->forceFill(['status' => MemberStatus::Suspended])->save();

    $this->get(route('member.dashboard'))->assertRedirect(route('login'));
    $this->assertGuest('member');
});

it('protects the member area and keeps member and admin sessions separate', function () {
    $this->get('/member/dashboard')->assertRedirect(route('login'));
    $this->get('/hi/member/dashboard')->assertRedirect(route('hi.login'));

    $this->actingAs(makeMember(), 'member');
    $this->get('/admin')->assertRedirect(route('admin.login'));
});

it('logs a member out', function () {
    $this->actingAs(makeMember(), 'member');
    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest('member');
});
