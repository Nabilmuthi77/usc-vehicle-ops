<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder default untuk lingkungan pengembangan & UAT.
 *
 * Produksi memakai `php artisan db:seed --class=ProductionSeeder`
 * sesuai PRD §12.3 langkah 6.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }
}
