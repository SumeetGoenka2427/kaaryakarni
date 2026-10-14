<?php

use App\Enums\MembershipStatus;
use App\Enums\MemberStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminUser;
use App\Models\Area;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\SmsLog;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\RazorpayGateway;
use App\Services\Settings\SettingsService;
use App\Services\Sms\SmsProvider;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakePaymentGateway;
use Tests\Support\FakeSmsProvider;

beforeEach(function () {
    Storage::fake('private');
    $this->seed([RolesAndPermissionsSeeder::class, NotificationTemplateSeeder::class]);
    $this->app->instance(SmsProvider::class, new FakeSmsProvider);
    $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    app(SettingsService::class)->update(['membership_fee' => 5100]);

    $area = Area::create(['name_en' => 'Chandpole', 'is_active' => true]);
    $this->member = tap(new Member(['name' => 'Ramesh Khandelwal', 'mobile' => '9876543210', 'area_id' => $area->id]), function ($m) {
        $m->status = MemberStatus::Verified;
        $m->verified_at = now();
        $m->profile_submitted_at = now();
        $m->save();
    })->fresh();
    $this->actingAs($this->member, 'member');
});

function startOnline($test): Payment
{
    $test->post('/member/membership/pay', ['mode' => 'online'])->assertRedirect();

    return Payment::latest('id')->first();
}

function callback($test, Payment $p, ?string $signature = null)
{
    return $test->post("/payments/{$p->ulid}/callback", [
        'razorpay_payment_id' => 'pay_'.$p->id, 'razorpay_order_id' => $p->provider_order_id,
        'razorpay_signature' => $signature ?? FakePaymentGateway::sign($p->provider_order_id, 'pay_'.$p->id),
    ]);
}

function webhook($test, array $payload, ?string $eventId = 'evt_1', ?string $signature = null)
{
    $body = json_encode($payload);

    return $test->call('POST', '/webhooks/razorpay', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_RAZORPAY_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, FakePaymentGateway::WEBHOOK_SECRET),
        'HTTP_X_RAZORPAY_EVENT_ID' => $eventId,
    ], $body);
}

it('shows lifetime membership with the fee and no renewal or expiry', function () {
    $html = $this->get('/member/membership')->assertOk()->assertSee('₹5,100')->assertSee('Lifetime — one-time, no renewal')->getContent();
    $this->get('/hi/member/membership')->assertOk()->assertSee('आजीवन');

    expect(strtolower(strip_tags($html)))->not->toContain('renew now')->not->toContain('expires on')->not->toContain('expiry date:');
});

it('completes an online payment only after a valid server-side signature', function () {
    $p = startOnline($this);
    expect($p->status)->toBe(PaymentStatus::Pending)->and((float) $p->amount)->toBe(5100.0);
    $this->get("/payments/{$p->ulid}/checkout")->assertOk()->assertSee($p->provider_order_id);

    callback($this, $p)->assertRedirect('/member/membership')->assertSessionHas('success');

    $p->refresh();
    $m = $this->member->fresh();
    expect($p->status)->toBe(PaymentStatus::Successful)
        ->and($p->receipt)->not->toBeNull()
        ->and(Membership::where('member_id', $m->id)->value('status'))->toBe(MembershipStatus::Active)
        ->and($m->membership_no)->toMatch('/^KSJ-\d{4}-0001$/')
        ->and(SmsLog::where('template_key', 'membership_active')->exists())->toBeTrue();
});

it('rejects a forged checkout signature', function () {
    $p = startOnline($this);
    callback($this, $p, 'forged-signature')->assertSessionHas('warning');

    expect($p->fresh()->status)->toBe(PaymentStatus::Pending)->and(Receipt::count())->toBe(0);
});

it('confirms via signed webhook, ignores replays and rejects bad signatures', function () {
    $p = startOnline($this);
    $payload = ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => 'pay_w1', 'order_id' => $p->provider_order_id, 'amount' => 510000, 'status' => 'captured']]]];

    webhook($this, $payload, 'evt_bad', 'bad-signature')->assertStatus(400);
    expect($p->fresh()->status)->toBe(PaymentStatus::Pending);

    webhook($this, $payload)->assertOk();
    webhook($this, $payload)->assertOk()->assertJson(['status' => 'duplicate ignored']);

    expect($p->fresh()->status)->toBe(PaymentStatus::Successful)->and(Receipt::count())->toBe(1);
});

it('refuses a webhook whose amount does not match', function () {
    $p = startOnline($this);
    webhook($this, ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => 'pay_x', 'order_id' => $p->provider_order_id, 'amount' => 100]]]])->assertOk();

    expect($p->fresh()->status)->toBe(PaymentStatus::Failed)->and(Receipt::count())->toBe(0);
});

it('is idempotent when callback and webhook both arrive', function () {
    $p = startOnline($this);
    callback($this, $p);
    webhook($this, ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['id' => 'pay_'.$p->id, 'order_id' => $p->provider_order_id, 'amount' => 510000]]]]);

    expect(Receipt::count())->toBe(1)->and(Payment::where('status', 'successful')->count())->toBe(1);
});

it('waits for profile verification before activating', function () {
    $this->member->forceFill(['status' => MemberStatus::UnderVerification, 'verified_at' => null])->save();
    $p = startOnline($this);
    callback($this, $p);

    expect(Membership::sole()->status)->toBe(MembershipStatus::UnderVerification)->and($this->member->fresh()->membership_no)->toBeNull();

    $admin = AdminUser::create(['name' => 'A', 'username' => 'a', 'email' => 'a@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('super-admin')->fresh();
    $this->actingAs($admin, 'admin')->patch(route('admin.members.verify', $this->member));

    expect(Membership::sole()->status)->toBe(MembershipStatus::Active)->and($this->member->fresh()->membership_no)->not->toBeNull();
});

it('handles an offline payment: submit → admin verify → receipt + lifetime', function () {
    $this->post('/member/membership/pay', [
        'mode' => 'offline', 'offline_method' => 'upi', 'offline_reference' => 'UTR123456',
        'offline_paid_on' => now()->toDateString(), 'proof' => UploadedFile::fake()->image('upi.png', 800, 1200),
    ])->assertRedirect('/member/membership');

    $p = Payment::sole();
    expect($p->status)->toBe(PaymentStatus::Submitted)->and(Membership::sole()->status)->toBe(MembershipStatus::PaymentSubmitted)
        ->and($p->proof['disk'])->toBe('private');

    $accounts = AdminUser::create(['name' => 'Acc', 'username' => 'acc', 'email' => 'acc@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('accounts')->fresh();
    $this->actingAs($accounts, 'admin');
    $this->get(route('admin.payments.proof', $p))->assertOk();
    $this->patch(route('admin.payments.verify', $p))->assertSessionHas('success');

    expect($p->fresh()->status)->toBe(PaymentStatus::Verified)->and($p->fresh()->receipt->receipt_no)->toStartWith('KSJ/')
        ->and(Membership::sole()->status)->toBe(MembershipStatus::Active);
});

it('lets admin reject an offline payment so the member can pay again', function () {
    $this->post('/member/membership/pay', ['mode' => 'offline', 'offline_method' => 'cheque', 'offline_reference' => 'CHQ001', 'offline_paid_on' => now()->toDateString()]);
    $p = Payment::sole();

    $admin = AdminUser::create(['name' => 'Acc', 'username' => 'acc', 'email' => 'acc@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('accounts')->fresh();
    $this->actingAs($admin, 'admin')->patch(route('admin.payments.reject', $p), ['reason' => 'Cheque bounced']);

    expect($p->fresh()->status)->toBe(PaymentStatus::Cancelled)->and(Membership::sole()->status)->toBe(MembershipStatus::PaymentPending);
    $this->actingAs($this->member, 'member')->get('/member/membership')->assertSee('Cheque bounced')->assertSee('Pay Now');
});

it('validates offline payment details', function () {
    $this->post('/member/membership/pay', ['mode' => 'offline', 'offline_method' => 'upi', 'offline_paid_on' => now()->addDay()->toDateString()])
        ->assertSessionHasErrors(['offline_reference', 'offline_paid_on']);
    expect(Payment::count())->toBe(0);
});

it('does not let a member pay twice once paid', function () {
    $p = startOnline($this);
    callback($this, $p);

    $this->get('/member/membership/pay')->assertRedirect('/member/membership');
    $this->post('/member/membership/pay', ['mode' => 'online'])->assertRedirect('/member/membership');
    expect(Payment::count())->toBe(1);
});

it('downloads the receipt PDF for the owner only', function () {
    $p = startOnline($this);
    callback($this, $p);

    $this->get("/member/payments/{$p->ulid}/receipt")->assertOk()->assertHeader('Content-Type', 'application/pdf');
    $this->get('/member/payments')->assertOk()->assertSee($p->receipt->receipt_no === null ? '' : 'Download');

    $other = tap(new Member(['name' => 'Other', 'mobile' => '9123456780']), fn ($m) => tap($m)->forceFill(['status' => MemberStatus::Verified])->save());
    $this->actingAs($other->fresh(), 'member');
    $this->get("/member/payments/{$p->ulid}/receipt")->assertNotFound();
    $this->get("/payments/{$p->ulid}/checkout")->assertNotFound();
});

it('numbers receipts sequentially per financial year', function () {
    $p1 = startOnline($this);
    callback($this, $p1);

    $second = tap(new Member(['name' => 'Second Member', 'mobile' => '9123456789']), fn ($m) => tap($m)->forceFill(['status' => MemberStatus::Verified])->save())->fresh();
    $this->actingAs($second, 'member');
    $p2 = startOnline($this);
    callback($this, $p2);

    [$a, $b] = Receipt::orderBy('id')->pluck('receipt_no')->all();
    expect((int) substr($b, -6))->toBe((int) substr($a, -6) + 1);
});

it('shows online payment as unavailable when the gateway is not configured', function () {
    $this->app->instance(PaymentGateway::class, new RazorpayGateway(['key_id' => null, 'key_secret' => null, 'webhook_secret' => null]));

    $this->get('/member/membership/pay')->assertOk()->assertSee('Online payment is not available');
    $this->post('/member/membership/pay', ['mode' => 'online'])->assertSessionHas('error');
    expect(Payment::count())->toBe(0);
});

it('keeps payment verification to accounts / super admins', function () {
    $this->post('/member/membership/pay', ['mode' => 'offline', 'offline_method' => 'cash', 'offline_paid_on' => now()->toDateString()]);
    $editor = AdminUser::create(['name' => 'Ed', 'username' => 'ed', 'email' => 'ed@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('content-manager')->fresh();

    $this->actingAs($editor, 'admin')->patch(route('admin.payments.verify', Payment::sole()))->assertForbidden();
    expect(Payment::sole()->status)->toBe(PaymentStatus::Submitted);
});

it('renders the admin membership, payment and receipt screens', function () {
    $p = startOnline($this);
    callback($this, $p);
    $admin = AdminUser::create(['name' => 'A', 'username' => 'a', 'email' => 'a@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('super-admin')->fresh();
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.memberships.index'))->assertOk()->assertSee('Lifetime Members');
    $this->get(route('admin.payments.index'))->assertOk()->assertSee('Ramesh Khandelwal');
    $this->get(route('admin.payments.show', $p))->assertOk()->assertSee('checkout.callback');
    $this->get(route('admin.receipts.index'))->assertOk()->assertSee($p->fresh()->receipt->receipt_no);
    $this->get(route('admin.receipts.download', $p->fresh()->receipt))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect($this->get(route('admin.payments.export'))->streamedContent())->toContain('Ramesh Khandelwal');

    $this->put(route('admin.memberships.fee'), ['membership_fee' => 6100])->assertSessionHas('success');
    expect(setting('membership_fee'))->toBe(6100)->and((float) Membership::sole()->fresh()->fee_amount)->toBe(5100.0);
});
