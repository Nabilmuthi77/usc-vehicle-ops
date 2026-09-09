<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk instalasi produksi (PRD §12.3 langkah 6).
 *
 * Hanya membuat data yang benar-benar dibutuhkan agar sistem dapat dipakai:
 * peran & permission, pengaturan sistem, master jenis servis, harga BBM,
 * satu departemen GA, serta satu akun Admin Sistem.
 *
 * Kata sandi awal diambil dari env `USC_VEHICLE_OPS_ADMIN_PASSWORD` dan wajib
 * diganti setelah login pertama.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingSeeder::class,
            ServiceTypeSeeder::class,
            FuelPriceSeeder::class,
        ]);

        $department = Department::firstOrCreate(
            ['code' => 'GA'],
            ['name' => 'GA & Umum', 'is_active' => true],
        );

        $email = env('USC_VEHICLE_OPS_ADMIN_EMAIL', 'admin@usc_vehicle_ops.local');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('USC_VEHICLE_OPS_ADMIN_NAME', 'Administrator USC_VEHICLE_OPS'),
                'username' => 'admin',
                'department_id' => $department->getKey(),
                'password' => Hash::make(env('USC_VEHICLE_OPS_ADMIN_PASSWORD', 'Admin@usc2026')),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles([User::ROLE_ADMIN_SISTEM]);

        $this->command?->warn(
            "Akun Admin Sistem dibuat: {$email}. Segera ganti kata sandinya setelah login pertama."
        );
    }
}
