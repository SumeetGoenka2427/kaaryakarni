<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Admin roles and permissions. Idempotent — safe to re-run on production.
 * "super-admin" passes every check via Gate::before (AppServiceProvider).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'members.view', 'members.manage',
        'areas.manage',
        'memberships.view', 'memberships.manage',
        'businesses.view', 'businesses.manage',
        'matrimonial.view', 'matrimonial.manage',
        'donations.view', 'donations.manage',
        'payments.view', 'payments.verify',
        'content.manage',
        'reports.view', 'reports.export',
        'notifications.send',
        'settings.manage',
        'admins.manage',
        'audit.view',
    ];

    public const ROLES = [
        'super-admin' => [],
        'membership-manager' => [
            'members.view', 'members.manage', 'areas.manage', 'memberships.view', 'memberships.manage',
            'payments.view', 'reports.view', 'notifications.send',
        ],
        'content-manager' => [
            'content.manage', 'businesses.view', 'businesses.manage', 'donations.view',
        ],
        'accounts' => [
            'payments.view', 'payments.verify', 'donations.view', 'donations.manage', 'memberships.view',
            'reports.view', 'reports.export',
        ],
        'matrimonial-moderator' => [
            'matrimonial.view', 'matrimonial.manage', 'members.view',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'admin');
        }

        foreach (self::ROLES as $role => $permissions) {
            Role::findOrCreate($role, 'admin')->syncPermissions($permissions);
        }
    }
}
