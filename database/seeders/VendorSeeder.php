<?php

namespace Database\Seeders;

use App\Enums\VendorType;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

/** FR-M1-06 — vendor bengkel, SPBU, dan penyedia sewa. */
class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            ['name' => 'Bengkel Resmi Auto2000', 'type' => VendorType::Bengkel, 'pic_name' => 'Pak Slamet', 'pic_phone' => '021-5551234', 'pic_email' => 'servis@auto2000.example', 'address' => 'Jl. MT Haryono No. 8, Jakarta'],
            ['name' => 'Bengkel Isuzu Astra', 'type' => VendorType::Bengkel, 'pic_name' => 'Bu Anita', 'pic_phone' => '021-5559876', 'pic_email' => 'service@isuzuastra.example', 'address' => 'Jl. Yos Sudarso No. 21, Jakarta'],
            ['name' => 'SPBU Pertamina 34.123', 'type' => VendorType::Spbu, 'pic_name' => 'Pak Herman', 'pic_phone' => '021-5552211', 'address' => 'Jl. Gatot Subroto, Jakarta'],
            ['name' => 'SPBU Shell Kuningan', 'type' => VendorType::Spbu, 'pic_name' => 'Bu Ratna', 'pic_phone' => '021-5553322', 'address' => 'Jl. HR Rasuna Said, Jakarta'],
            ['name' => 'CV Sewa Armada Jaya', 'type' => VendorType::Leasing, 'pic_name' => 'Pak Yusuf', 'pic_phone' => '021-5554455', 'pic_email' => 'operasional@armadajaya.example', 'address' => 'Jl. Casablanca Raya No. 10, Jakarta'],
            ['name' => 'PT Trac Astra Rent', 'type' => VendorType::Leasing, 'pic_name' => 'Bu Melati', 'pic_phone' => '021-5556677', 'pic_email' => 'fleet@tracastra.example', 'address' => 'Jl. TB Simatupang, Jakarta'],
            ['name' => 'Bengkel Honda Cilandak', 'type' => VendorType::Bengkel, 'pic_name' => 'Pak Rio', 'pic_phone' => '021-5558899', 'address' => 'Jl. Cilandak KKO, Jakarta', 'is_active' => false],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(
                ['name' => $vendor['name']],
                $vendor + ['is_active' => true],
            );
        }
    }
}
