<?php

use App\Enums\DonationStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminUser;
use App\Models\BannerPanel;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Payment;
use App\Models\Receipt;
use App\Services\Content\PublicContent;
use App\Services\Payments\PaymentGateway;
use App\Services\Settings\SettingsService;
use App\Services\Sms\SmsProvider;
use Database\Seeders\DonationCampaignSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\WebsiteContentSeeder;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakePaymentGateway;
use Tests\Support\FakeSmsProvider;

beforeEach(function () {
    Storage::fake('private');
    Storage::fake('public');
    $this->seed([RolesAndPermissionsSeeder::class, NotificationTemplateSeeder::class, DonationCampaignSeeder::class, WebsiteContentSeeder::class]);
    $this->app->instance(SmsProvider::class, new FakeSmsProvider);
    $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    $this->samaj = DonationCampaign::where('is_permanent', true)->sole();
    $this->diwali = tap(new DonationCampaign(['slug' => 'diwali-utsav', 'title_en' => 'Diwali Utsav Donation', 'title_hi' => 'दीवाली उत्सव दान',
        'target_amount' => 100000, 'allow_online' => true, 'allow_offline' => true, 'has_tickets' => true,
        'ticket_label_en' => 'Entry Pass', 'ticket_price' => 501, 'sort_order' => 1]), fn ($c) => tap($c)->forceFill(['is_active' => true])->save());
});

function donor(array $extra = []): array
{
    return array_merge(['campaign' => 'samaj-donation', 'type' => 'donation', 'amount' => 2100, 'donor_name' => 'Suresh Singhal',
        'donor_mobile' => '9000000013', 'mode' => 'online'], $extra);
}

function accountsAdmin(): AdminUser
{
    return AdminUser::create(['name' => 'Acc', 'username' => 'acc', 'email' => 'acc@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('accounts')->fresh();
}

it('always has the permanent Samaj Donation, which cannot be deleted or deactivated', function () {
    $this->get('/donations')->assertOk()->assertSee('Samaj Donation')->assertSee('Permanent Campaign');
    $this->get('/hi/donations')->assertOk()->assertSee('समाज दान');

    expect(fn () => $this->samaj->delete())->toThrow(LogicException::class);
    expect(fn () => $this->samaj->forceFill(['is_active' => false])->save())->toThrow(LogicException::class);

    $admin = AdminUser::create(['name' => 'S', 'username' => 's', 'email' => 's@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('super-admin')->fresh();
    $this->actingAs($admin, 'admin');
    $this->delete(route('admin.campaigns.destroy', $this->samaj->id))->assertSessionHas('error');
    $this->patch(route('admin.campaigns.toggle', $this->samaj->id))->assertSessionHas('error');
    expect(DonationCampaign::where('is_permanent', true)->where('is_active', true)->exists())->toBeTrue();
});

it('lets a guest donate online, confirmed only by a verified signature, with live totals', function () {
    $this->post('/donations', donor())->assertRedirect();
    $p = Payment::sole();
    expect(Donation::sole()->status)->toBe(DonationStatus::Pending)->and((float) $this->samaj->fresh()->raised_amount)->toBe(0.0);

    $this->get("/payments/{$p->ulid}/checkout")->assertOk();
    $this->post("/payments/{$p->ulid}/callback", ['razorpay_payment_id' => 'pay_d1', 'razorpay_order_id' => $p->provider_order_id,
        'razorpay_signature' => FakePaymentGateway::sign($p->provider_order_id, 'pay_d1')])->assertRedirect('/donations/samaj-donation');

    $c = $this->samaj->fresh();
    expect(Donation::sole()->status)->toBe(DonationStatus::Successful)->and((float) $c->raised_amount)->toBe(2100.0)
        ->and($c->donor_count)->toBe(1)->and(Receipt::count())->toBe(1);
});

it("stops another browser from opening a guest's checkout", function () {
    $this->post('/donations', donor());
    $p = Payment::sole();
    $this->flushSession();

    $this->get("/payments/{$p->ulid}/checkout")->assertNotFound();
});

it('handles an offline donation: submitted → verified by accounts → totals + receipt', function () {
    $this->post('/donations', donor(['mode' => 'offline', 'offline_method' => 'cheque', 'offline_reference' => 'CHQ778', 'offline_paid_on' => now()->toDateString()]))
        ->assertRedirect('/donations/samaj-donation');
    $d = Donation::sole();
    expect($d->status)->toBe(DonationStatus::Submitted)->and((float) $this->samaj->fresh()->raised_amount)->toBe(0.0);

    $this->actingAs(accountsAdmin(), 'admin')->patch(route('admin.payments.verify', $d->payment))->assertSessionHas('success');

    expect($d->fresh()->status)->toBe(DonationStatus::Verified)->and((float) $this->samaj->fresh()->raised_amount)->toBe(2100.0)
        ->and($d->fresh()->payment->receipt)->not->toBeNull();
});

it('sells fixed-price tickets and ignores a submitted amount', function () {
    $this->post('/donations', donor(['campaign' => 'diwali-utsav', 'type' => 'ticket', 'quantity' => 3, 'amount' => 1]))->assertRedirect();

    $d = Donation::sole();
    expect($d->type)->toBe('ticket')->and($d->quantity)->toBe(3)->and((float) $d->amount)->toBe(1503.0)->and((float) $d->payment->amount)->toBe(1503.0);
});

it('rejects tickets for campaigns without tickets, closed campaigns and disabled modes', function () {
    $this->post('/donations', donor(['type' => 'ticket', 'quantity' => 1]))->assertSessionHas('error');

    $this->diwali->update(['ends_at' => now()->subDay()]);
    $this->post('/donations', donor(['campaign' => 'diwali-utsav']))->assertSessionHas('error');

    $this->samaj->update(['allow_online' => false]);
    $this->post('/donations', donor())->assertSessionHas('error');
    expect(Donation::count())->toBe(0);
});

it('validates donor details', function () {
    $this->post('/donations', donor(['donor_mobile' => '123', 'amount' => 0, 'donor_pan' => 'BADPAN', 'campaign' => 'nope']))
        ->assertSessionHasErrors(['donor_mobile', 'amount', 'donor_pan', 'campaign']);
});

it('respects donor privacy on public pages', function () {
    $this->post('/donations', donor(['mode' => 'offline', 'offline_method' => 'cash', 'offline_paid_on' => now()->toDateString(), 'donor_name' => 'Visible Donor']));
    $this->post('/donations', donor(['mode' => 'offline', 'offline_method' => 'cash', 'offline_paid_on' => now()->toDateString(), 'donor_name' => 'Hidden Donor', 'donor_mobile' => '9000000099', 'is_anonymous' => '1']));
    $this->actingAs(accountsAdmin(), 'admin');
    Donation::with('payment')->get()->each(fn ($d) => $this->patch(route('admin.payments.verify', $d->payment)));

    $this->get('/donations/samaj-donation')->assertSee('Visible Donor')->assertDontSee('Hidden Donor')->assertSee('A Samaj member');

    app(SettingsService::class)->update(['donor_names_public' => false]);
    PublicContent::flush();
    $this->get('/donations/samaj-donation')->assertDontSee('Visible Donor');
});

it('records refunds and reduces totals; recalculate repairs drift', function () {
    $this->post('/donations', donor(['mode' => 'offline', 'offline_method' => 'cash', 'offline_paid_on' => now()->toDateString()]));
    $d = Donation::sole();
    $admin = AdminUser::create(['name' => 'S', 'username' => 's', 'email' => 's@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('super-admin')->fresh();
    $this->actingAs($admin, 'admin')->patch(route('admin.payments.verify', $d->payment));

    $this->patch(route('admin.donations.refund', $d), ['reason' => 'Duplicate payment returned'])->assertSessionHas('success');
    expect($d->fresh()->status)->toBe(DonationStatus::Refunded)->and($d->fresh()->payment->status)->toBe(PaymentStatus::Refunded)
        ->and((float) $this->samaj->fresh()->raised_amount)->toBe(0.0)->and($this->samaj->fresh()->donor_count)->toBe(0);

    $this->samaj->forceFill(['raised_amount' => 999999, 'donor_count' => 50])->saveQuietly();
    $this->artisan('donations:recalculate')->assertSuccessful();
    expect((float) $this->samaj->fresh()->raised_amount)->toBe(0.0);
});

it('shows live donation data on the homepage banner panels and campaign section', function () {
    $this->post('/donations', donor(['mode' => 'offline', 'offline_method' => 'cash', 'offline_paid_on' => now()->toDateString(), 'amount' => 25000]));
    $this->actingAs(accountsAdmin(), 'admin')->patch(route('admin.payments.verify', Donation::sole()->payment));
    BannerPanel::where('position', 'right')->update(['mode' => 'campaign_compact', 'campaign_id' => $this->samaj->id]);
    PublicContent::flush();

    $this->get('/')->assertOk()->assertSee('₹25,000')->assertSee('Active Donation Campaigns')->assertSee('Diwali Utsav Donation')->assertSee('Donations Collected');
});

it('manages campaigns from admin', function () {
    $admin = AdminUser::create(['name' => 'S', 'username' => 's', 'email' => 's@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('super-admin')->fresh();
    $this->actingAs($admin, 'admin');

    $this->post(route('admin.campaigns.store'), ['title_en' => 'Gaushala Seva', 'title_hi' => 'गौशाला सेवा', 'target_amount' => 50000,
        'allow_online' => '1', 'allow_offline' => '1', 'is_active' => '1'])->assertRedirect(route('admin.campaigns.index'));
    $c = DonationCampaign::where('slug', 'gaushala-seva')->sole();
    $this->get('/donations')->assertSee('Gaushala Seva');
    $this->get('/hi/donations')->assertSee('गौशाला सेवा');

    $this->patch(route('admin.campaigns.toggle', $c->id));
    $this->get(route('admin.campaigns.index')); // consumes the "deactivated" flash message
    $this->get('/donations')->assertDontSee('Gaushala Seva');

    $this->delete(route('admin.campaigns.destroy', $c->id))->assertSessionHas('success');
    $this->get(route('admin.donations.index'))->assertOk();
    $this->get(route('admin.campaigns.index'))->assertOk()->assertSee('Permanent');
});

it('keeps campaign management to donation managers', function () {
    $editor = AdminUser::create(['name' => 'Ed', 'username' => 'ed', 'email' => 'ed@x.org', 'password' => 'Correct-Horse-1', 'is_active' => true])->assignRole('membership-manager')->fresh();

    $this->actingAs($editor, 'admin')->get(route('admin.campaigns.index'))->assertForbidden();
    $this->post(route('admin.campaigns.store'), ['title_en' => 'X'])->assertForbidden();
});
