<?php

use App\Models\AdminUser;
use App\Models\AuditLog;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->super = AdminUser::create([
        'name' => 'Super', 'username' => 'super', 'email' => 's@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('super-admin')->fresh();
    $this->actingAs($this->super, 'admin');
});

it('lists admins', function () {
    $this->get(route('admin.admin-users.index'))->assertOk()->assertSee('super')->assertSee('Super Admin (everything)');
});

it('creates an admin with a role and a strong password', function () {
    $this->post(route('admin.admin-users.store'), [
        'name' => 'Accounts Person', 'username' => 'accounts1', 'email' => 'a@example.org',
        'role' => 'accounts', 'password' => 'Strong-Pass-99', 'password_confirmation' => 'Strong-Pass-99',
    ])->assertRedirect(route('admin.admin-users.index'));

    $admin = AdminUser::where('username', 'accounts1')->sole();
    expect($admin->hasRole('accounts'))->toBeTrue()
        ->and(Hash::check('Strong-Pass-99', $admin->password))->toBeTrue()
        ->and(AuditLog::where('action', 'admin_user.created')->count())->toBe(1);
});

it('rejects weak passwords and duplicate usernames', function () {
    $this->post(route('admin.admin-users.store'), [
        'name' => 'X', 'username' => 'super', 'email' => 'x@example.org',
        'role' => 'accounts', 'password' => 'short', 'password_confirmation' => 'short',
    ])->assertSessionHasErrors(['username', 'password']);
});

it('stops an admin from deactivating or demoting themselves', function () {
    $this->put(route('admin.admin-users.update', $this->super), [
        'name' => 'Super', 'email' => 's@example.org', 'role' => 'accounts', 'is_active' => '1',
    ])->assertSessionHasErrors('role');

    expect($this->super->fresh()->hasRole('super-admin'))->toBeTrue();
});

it('keeps at least one active super admin', function () {
    $other = AdminUser::create([
        'name' => 'Other', 'username' => 'other', 'email' => 'o@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('super-admin');
    $this->super->syncRoles(['accounts']);
    $this->super->givePermissionTo('admins.manage');

    $this->put(route('admin.admin-users.update', $other), [
        'name' => 'Other', 'email' => 'o@example.org', 'role' => 'super-admin', 'is_active' => '0',
    ])->assertSessionHasErrors('role');
});

it('resets a password and deactivates another admin, with an audit trail', function () {
    $editor = AdminUser::create([
        'name' => 'Editor', 'username' => 'editor', 'email' => 'e@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('content-manager');

    $this->put(route('admin.admin-users.update', $editor), [
        'name' => 'Editor', 'email' => 'e@example.org', 'role' => 'content-manager', 'is_active' => '0',
        'password' => 'New-Strong-77', 'password_confirmation' => 'New-Strong-77',
    ])->assertRedirect(route('admin.admin-users.index'));

    $editor->refresh();
    expect($editor->is_active)->toBeFalse()->and(Hash::check('New-Strong-77', $editor->password))->toBeTrue();

    $log = AuditLog::where('action', 'admin_user.updated')->sole();
    expect(json_encode($log->new_values))->not->toContain('New-Strong-77');
});

it('does not allow changing a username', function () {
    $this->put(route('admin.admin-users.update', $this->super), [
        'name' => 'Super', 'username' => 'hijack', 'email' => 's@example.org', 'role' => 'super-admin', 'is_active' => '1',
    ])->assertSessionHasErrors('username');
});

it('is only for admins with the admins.manage permission', function () {
    $editor = AdminUser::create([
        'name' => 'Editor', 'username' => 'editor', 'email' => 'e@example.org', 'password' => 'Correct-Horse-1', 'is_active' => true,
    ])->assignRole('content-manager')->fresh();

    $this->actingAs($editor, 'admin')->get(route('admin.admin-users.index'))->assertForbidden();
});
