<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

/** FR-M1-05 — departemen / cost center contoh. */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'GA', 'name' => 'GA & Umum', 'pic_name' => 'Budi Santoso'],
            ['code' => 'MKT', 'name' => 'Marketing', 'pic_name' => 'Agus Wijaya'],
            ['code' => 'DIR', 'name' => 'Direksi', 'pic_name' => 'Maya Puspita'],
            ['code' => 'LOG', 'name' => 'Logistik', 'pic_name' => 'Nina Kartika'],
            ['code' => 'FIN', 'name' => 'Finance', 'pic_name' => 'Siti Rahma'],
            ['code' => 'HRD', 'name' => 'HRD', 'pic_name' => 'Fajar Nugroho'],
            ['code' => 'IT', 'name' => 'IT', 'pic_name' => 'Nabil Muthi Maulani'],
        ];

        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department + ['is_active' => true],
            );
        }
    }
}
