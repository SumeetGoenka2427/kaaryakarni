<?php

use App\Models\AdminUser;
use App\Models\AuditLog;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = AdminUser::create([
        'name' => 'Test Admin', 'username' => 'tester', 'email' => 't@example.org',
        'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('super-admin')->fresh();
});

it('redirects guests from the admin panel to the admin login', function () {
    $this->get('/admin')->assertRedirect(route('admin.login'));
    $this->get('/admin/settings')->assertRedirect(route('admin.login'));
});

it('logs in with the correct password and records it in the audit log', function () {
    $this->post(route('admin.login.store'), ['username' => 'tester', 'password' => 'Correct-Horse-1'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($this->admin, 'admin');
    expect(AuditLog::where('action', 'admin.login')->count())->toBe(1)
        ->and($this->admin->fresh()->last_login_at)->not->toBeNull();
});

it('rejects a wrong password with a generic message', function () {
    $this->from(route('admin.login'))
        ->post(route('admin.login.store'), ['username' => 'tester', 'password' => 'wrong'])
        ->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors(['username' => 'Invalid username or password.']);

    $this->assertGuest('admin');
});

it('locks the account out after 5 failed attempts', function () {
    foreach (range(1, 5) as $i) {
        $this->post(route('admin.login.store'), ['username' => 'tester', 'password' => 'wrong']);
    }

    // Even the correct password is refused while locked out.
    $this->post(route('admin.login.store'), ['username' => 'tester', 'password' => 'Correct-Horse-1'])
        ->assertSessionHasErrors('username');
    $this->assertGuest('admin');
    expect(session('errors')->first('username'))->toContain('Too many login attempts');
});

it('does not let an inactive admin log in', function () {
    $this->admin->update(['is_active' => false]);

    $this->post(route('admin.login.store'), ['username' => 'tester', 'password' => 'Correct-Horse-1'])
        ->assertSessionHasErrors(['username' => 'Invalid username or password.']);
    $this->assertGuest('admin');
});

it('logs out an admin after the idle timeout', function () {
    $this->actingAs($this->admin, 'admin')
        ->withSession(['admin_last_activity' => time() - (config('portal.admin.idle_timeout') * 60) - 5])
        ->get('/admin')
        ->assertRedirect(route('admin.login'));

    $this->assertGuest('admin');
});

it('logs out and invalidates the session', function () {
    $this->actingAs($this->admin, 'admin')->post(route('admin.logout'))->assertRedirect(route('admin.login'));
    $this->assertGuest('admin');
});

it('blocks admins without the permission (server-side, not just hidden menus)', function () {
    $editor = AdminUser::create([
        'name' => 'Content Editor', 'username' => 'editor', 'email' => 'e@example.org',
        'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('content-manager')->fresh();

    $this->actingAs($editor, 'admin');
    $this->get(route('admin.settings.edit'))->assertForbidden();
    $this->put(route('admin.settings.update'), ['org_name_en' => 'Hacked'])->assertForbidden();
    $this->get(route('admin.branding.edit'))->assertForbidden();
    $this->get(route('admin.site-texts.index'))->assertOk();

    // The sidebar only shows what this admin may open.
    $this->get(route('admin.dashboard'))->assertOk()
        ->assertSee('Website Texts (EN / HI)')
        ->assertDontSee('Branding &amp; Logo', false);
});
