<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

/** FR-M4-01 — master jenis servis dengan interval bawaan. */
class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Servis Ringan',
                'description' => 'Pemeriksaan berkala menyeluruh dan penyetelan ringan.',
                'default_interval_km' => 5000,
                'default_interval_months' => 3,
            ],
            [
                'name' => 'Ganti Oli Mesin',
                'description' => 'Penggantian oli mesin beserta filter oli.',
                'default_interval_km' => 10000,
                'default_interval_months' => 6,
            ],
            [
                'name' => 'Ganti Filter Udara',
                'description' => 'Penggantian filter udara dan pembersihan saluran udara.',
                'default_interval_km' => 20000,
                'default_interval_months' => 12,
            ],
            [
                'name' => 'Ganti Ban',
                'description' => 'Penggantian ban beserta spooring dan balancing.',
                'default_interval_km' => 40000,
                'default_interval_months' => 36,
            ],
        ];

        foreach ($types as $type) {
            ServiceType::updateOrCreate(['name' => $type['name']], $type + ['is_active' => true]);
        }
    }
}
