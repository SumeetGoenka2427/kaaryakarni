<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local development: production essentials (+ demo data once DemoSeeder exists, P05+).
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);
    }
}
