<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Data the live site needs to run. Idempotent and contains no demo data.
 * Run on production with: php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolesAndPermissionsSeeder::class, NotificationTemplateSeeder::class, WebsiteContentSeeder::class]);

        $seed = config('portal.admin.seed');

        if (! AdminUser::where('username', $seed['username'])->exists()) {
            if (blank($seed['password']) || strlen($seed['password']) < 10) {
                throw new RuntimeException('Set ADMIN_SEED_PASSWORD (min 10 characters) in .env before seeding the first admin.');
            }

            AdminUser::create([
                'name' => $seed['name'],
                'username' => $seed['username'],
                'email' => $seed['email'] ?: $seed['username'].'@localhost',
                'password' => $seed['password'],
                'is_active' => true,
            ])->assignRole('super-admin');

            $this->command?->info("Super admin '{$seed['username']}' created. Change the password after first login.");
        }
    }
}
