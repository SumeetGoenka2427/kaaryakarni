<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local development: production essentials + clearly-marked sample data.
     */
    public function run(): void
    {
        $this->call([
            ProductionSeeder::class,
            SampleAreasSeeder::class,
            SampleWebsiteSeeder::class,
            SampleMembersSeeder::class,
        ]);
    }
}
