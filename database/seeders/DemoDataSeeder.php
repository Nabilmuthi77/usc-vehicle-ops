<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\DurationType;
use App\Enums\FuelClaimStatus;
use App\Enums\FuelType;
use App\Enums\Ownership;
use App\Enums\PaymentMethod;
use App\Enums\TollCardStatus;
use App\Enums\VehicleClass;
use App\Enums\VehicleDocumentType;
use App\Enums\VehicleStatus;
use App\Models\Booking;
use App\Models\Department;
use App\Models\Driver;
use App\Models\FuelTransaction;
use App\Models\RentalContract;
use App\Models\TollCard;
use App\Models\TollTransaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\Vendor;
use App\Services\FuelAnomalyDetector;
use App\Services\FuelConsumptionService;
use App\Services\ServiceScheduleService;
use App\Services\TollBalanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh untuk pengembangan & UAT.
 *
 * Isinya mengikuti data yang sebelumnya dipakai prototipe antarmuka
 * (`App\Support\FleetDummyData`) sehingga tampilan tetap familier, namun
 * kini tersimpan sebagai data nyata melalui Eloquent.
 *
 * JANGAN dijalankan di server produksi — pakai `ProductionSeeder`.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingSeeder::class,
            ServiceTypeSeeder::class,
            FuelPriceSeeder::class,
            DepartmentSeeder::class,
            VendorSeeder::class,
        ]);

        $departments = Department::pluck('id', 'name');
        $vendors = Vendor::pluck('id', 'name');

        $contracts = $this->seedRentalContracts($vendors);
        $users = $this->seedUsers($departments);
        $drivers = $this->seedDrivers($users);
        $vehicles = $this->seedVehicles($departments, $contracts);

        $this->seedVehicleDocuments($vehicles);
        $this->seedTollCards($vehicles);
        $this->seedBookings($users, $drivers, $vehicles, $departments);
        $this->seedFuelTransactions($vehicles, $drivers, $users);
        $this->seedTollTransactions($vehicles);
        $this->seedServiceSchedules($vehicles);

        $this->command?->info('Data contoh USC_VEHICLE_OPS berhasil dibuat.');
    }

    /** @return array<string, int> */
    private function seedRentalContracts($vendors): array
    {
        $rows = [
            ['contract_number' => 'KTR/2025/014', 'vendor' => 'CV Sewa Armada Jaya', 'start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'monthly_cost' => 8500000],
            ['contract_number' => 'KTR/2024/031', 'vendor' => 'PT Trac Astra Rent', 'start_date' => '2025-01-01', 'end_date' => '2026-12-31', 'monthly_cost' => 11500000],
            ['contract_number' => 'KTR/2025/002', 'vendor' => 'PT Trac Astra Rent', 'start_date' => '2025-01-15', 'end_date' => '2027-01-14', 'monthly_cost' => 13200000],
            ['contract_number' => 'KTR/2025/019', 'vendor' => 'CV Sewa Armada Jaya', 'start_date' => '2025-03-01', 'end_date' => '2026-02-28', 'monthly_cost' => 6200000],
        ];

        $result = [];

        foreach ($rows as $row) {
            $vendorName = $row['vendor'];
            unset($row['vendor']);

            $contract = RentalContract::updateOrCreate(
                ['contract_number' => $row['contract_number']],
                $row + [
                    'vendor_id' => $vendors[$vendorName],
                    'coverage' => ['servis' => true, 'ban' => true, 'asuransi' => true, 'pengganti_unit' => false],
                    'reminder_days' => 60,
                    'is_active' => true,
                ],
            );

            $result[$contract->contract_number] = $contract->getKey();
        }

        return $result;
    }

    /** @return array<string, User> */
    private function seedUsers($departments): array
    {
        $rows = [
            ['name' => 'Slamet Riyadi', 'username' => 'slamet', 'email' => 'slamet.riyadi@company.co.id', 'department' => 'GA & Umum', 'role' => User::ROLE_ADMIN_SISTEM],
            ['name' => 'Admin GA', 'username' => 'adminga', 'email' => 'admin@usc_vehicle_ops.local', 'department' => 'GA & Umum', 'role' => User::ROLE_ADMIN_GA],
            ['name' => 'Dewi Anggraini', 'username' => 'dewi', 'email' => 'dewi.anggraini@company.co.id', 'department' => 'Marketing', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Nabil Muthi Maulani', 'username' => 'nabil', 'email' => 'nabilmuthi77@gmail.com', 'department' => 'IT', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Budi Santoso', 'username' => 'budi', 'email' => 'budi.santoso@company.co.id', 'department' => 'GA & Umum', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Agus Wijaya', 'username' => 'agus', 'email' => 'agus.wijaya@company.co.id', 'department' => 'Marketing', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Nina Kartika', 'username' => 'nina', 'email' => 'nina.kartika@company.co.id', 'department' => 'Logistik', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Dimas Prasetyo', 'username' => 'dimas', 'email' => 'dimas.prasetyo@company.co.id', 'department' => 'IT', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Lestari Wulandari', 'username' => 'lestari', 'email' => 'lestari.w@company.co.id', 'department' => 'Marketing', 'role' => User::ROLE_KARYAWAN],
            ['name' => 'Fajar Nugroho', 'username' => 'fajar', 'email' => 'fajar.nugroho@company.co.id', 'department' => 'HRD', 'role' => User::ROLE_KARYAWAN, 'is_active' => false],
            ['name' => 'Joko Wibowo', 'username' => 'joko', 'email' => 'joko.wibowo@company.co.id', 'department' => 'GA & Umum', 'role' => User::ROLE_DRIVER],
            ['name' => 'Hendra Saputra', 'username' => 'hendra', 'email' => 'hendra.saputra@company.co.id', 'department' => 'GA & Umum', 'role' => User::ROLE_DRIVER],
            ['name' => 'Ahmad Fauzi', 'username' => 'ahmad', 'email' => 'ahmad.fauzi@company.co.id', 'department' => 'GA & Umum', 'role' => User::ROLE_DRIVER],
            ['name' => 'Rudi Hartono', 'username' => 'rudi', 'email' => 'rudi.hartono@company.co.id', 'department' => 'GA & Umum', 'role' => User::ROLE_DRIVER],
            ['name' => 'Eko Prasetyo', 'username' => 'eko', 'email' => 'eko.prasetyo@company.co.id', 'department' => 'GA & Umum', 'role' => User::ROLE_DRIVER],
            ['name' => 'Maya Puspita', 'username' => 'maya', 'email' => 'maya.puspita@company.co.id', 'department' => 'Direksi', 'role' => User::ROLE_VIEWER],
            ['name' => 'Siti Rahma', 'username' => 'siti', 'email' => 'siti.rahma@company.co.id', 'department' => 'Finance', 'role' => User::ROLE_VIEWER],
        ];

        $users = [];

        foreach ($rows as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'department_id' => $departments[$row['department']] ?? null,
                    'password' => Hash::make('password'),
                    'is_active' => $row['is_active'] ?? true,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$row['role']]);
            $users[$row['name']] = $user;
        }

        return $users;
    }

    /** @return array<string, Driver> */
    private function seedDrivers(array $users): array
    {
        $rows = [
            ['name' => 'Joko Wibowo', 'employee_id' => 'DRV-001', 'license_number' => '5301021107850001', 'license_type' => 'SIM B1 Umum', 'license_expiry' => '2027-02-14', 'phone' => '0812-3456-7801'],
            ['name' => 'Hendra Saputra', 'employee_id' => 'DRV-002', 'license_number' => '5301021107880002', 'license_type' => 'SIM B2 Umum', 'license_expiry' => '2026-09-02', 'phone' => '0812-3456-7802'],
            ['name' => 'Ahmad Fauzi', 'employee_id' => 'DRV-003', 'license_number' => '5301021107900003', 'license_type' => 'SIM A Umum', 'license_expiry' => '2026-08-28', 'phone' => '0812-3456-7803'],
            ['name' => 'Rudi Hartono', 'employee_id' => 'DRV-004', 'license_number' => '5301021107820004', 'license_type' => 'SIM B1 Umum', 'license_expiry' => '2026-12-19', 'phone' => '0812-3456-7804'],
            ['name' => 'Eko Prasetyo', 'employee_id' => 'DRV-005', 'license_number' => '5301021107930005', 'license_type' => 'SIM A Umum', 'license_expiry' => '2027-03-05', 'phone' => '0812-3456-7805'],
            ['name' => 'Wahyu Setiawan', 'employee_id' => 'DRV-006', 'license_number' => '5301021107870006', 'license_type' => 'SIM B2 Umum', 'license_expiry' => '2026-08-30', 'phone' => '0812-3456-7806'],
            ['name' => 'Bambang Irawan', 'employee_id' => 'DRV-007', 'license_number' => '5301021107790007', 'license_type' => 'SIM B1 Umum', 'license_expiry' => '2026-01-11', 'phone' => '0812-3456-7807', 'is_active' => false],
            ['name' => 'Dedi Kurniawan', 'employee_id' => 'DRV-008', 'license_number' => '5301021107910008', 'license_type' => 'SIM A Umum', 'license_expiry' => '2026-10-22', 'phone' => '0812-3456-7808'],
        ];

        $drivers = [];

        foreach ($rows as $row) {
            // Sebagian driver lapangan tidak memiliki akun sistem (user_id null).
            $driver = Driver::updateOrCreate(
                ['employee_id' => $row['employee_id']],
                $row + [
                    'user_id' => ($users[$row['name']] ?? null)?->getKey(),
                    'is_active' => $row['is_active'] ?? true,
                ],
            );

            $drivers[$driver->name] = $driver;
        }

        return $drivers;
    }

    /** @return array<string, Vehicle> */
    private function seedVehicles($departments, array $contracts): array
    {
        $rows = [
            ['plate_number' => 'B 1234 XI', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2022, 'ownership' => Ownership::Milik, 'department' => 'GA & Umum', 'status' => VehicleStatus::Tersedia, 'current_odometer' => 45230, 'fuel_type' => FuelType::Pertalite, 'tank_capacity' => 45],
            ['plate_number' => 'B 2087 KJ', 'brand' => 'Daihatsu', 'model' => 'Xenia', 'year' => 2021, 'ownership' => Ownership::Milik, 'department' => 'Marketing', 'status' => VehicleStatus::Dipinjam, 'current_odometer' => 62110, 'fuel_type' => FuelType::Pertalite, 'tank_capacity' => 45],
            ['plate_number' => 'B 9012 CD', 'brand' => 'Toyota', 'model' => 'Innova', 'year' => 2023, 'ownership' => Ownership::Sewa, 'department' => 'Direksi', 'status' => VehicleStatus::Tersedia, 'current_odometer' => 18450, 'fuel_type' => FuelType::Pertamax, 'tank_capacity' => 55, 'contract' => 'KTR/2025/014'],
            ['plate_number' => 'B 3345 QW', 'brand' => 'Mitsubishi', 'model' => 'Colt Diesel', 'year' => 2020, 'ownership' => Ownership::Milik, 'department' => 'Logistik', 'status' => VehicleStatus::Servis, 'current_odometer' => 98700, 'fuel_type' => FuelType::Solar, 'tank_capacity' => 70],
            ['plate_number' => 'B 4456 ZP', 'brand' => 'Honda', 'model' => 'Brio', 'year' => 2022, 'ownership' => Ownership::Milik, 'department' => 'Finance', 'status' => VehicleStatus::Tersedia, 'current_odometer' => 31220, 'fuel_type' => FuelType::Pertalite, 'tank_capacity' => 35],
            ['plate_number' => 'B 5501 GA', 'brand' => 'Toyota', 'model' => 'Hiace', 'year' => 2021, 'ownership' => Ownership::Leasing, 'department' => 'GA & Umum', 'status' => VehicleStatus::Dipinjam, 'current_odometer' => 74300, 'fuel_type' => FuelType::Solar, 'tank_capacity' => 70, 'contract' => 'KTR/2024/031'],
            ['plate_number' => 'B 6678 RS', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2019, 'ownership' => Ownership::Milik, 'department' => 'Marketing', 'status' => VehicleStatus::Nonaktif, 'current_odometer' => 152300, 'fuel_type' => FuelType::Pertalite, 'tank_capacity' => 45],
            ['plate_number' => 'B 7789 TN', 'brand' => 'Daihatsu', 'model' => 'Grand Max Box', 'year' => 2022, 'ownership' => Ownership::Sewa, 'department' => 'Logistik', 'status' => VehicleStatus::Tersedia, 'current_odometer' => 40010, 'fuel_type' => FuelType::Pertalite, 'tank_capacity' => 43, 'contract' => 'KTR/2025/019'],
            ['plate_number' => 'B 8890 MK', 'brand' => 'Honda', 'model' => 'CR-V', 'year' => 2023, 'ownership' => Ownership::Milik, 'department' => 'Direksi', 'status' => VehicleStatus::Tersedia, 'current_odometer' => 12040, 'fuel_type' => FuelType::Pertamax, 'tank_capacity' => 57],
            ['plate_number' => 'B 1123 EE', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2020, 'ownership' => Ownership::Milik, 'department' => 'HRD', 'status' => VehicleStatus::Dipinjam, 'current_odometer' => 88750, 'fuel_type' => FuelType::Pertalite, 'tank_capacity' => 45],
            ['plate_number' => 'B 2234 FF', 'brand' => 'Isuzu', 'model' => 'Elf Bus', 'year' => 2021, 'ownership' => Ownership::Leasing, 'department' => 'GA & Umum', 'status' => VehicleStatus::Servis, 'current_odometer' => 66500, 'fuel_type' => FuelType::Solar, 'tank_capacity' => 76, 'contract' => 'KTR/2025/002'],
            ['plate_number' => 'B 3390 GG', 'brand' => 'Suzuki', 'model' => 'Ertiga', 'year' => 2022, 'ownership' => Ownership::Milik, 'department' => 'IT', 'status' => VehicleStatus::Tersedia, 'current_odometer' => 28900, 'fuel_type' => FuelType::Pertalite, 'tank_capacity' => 45],
        ];

        $vehicles = [];

        foreach ($rows as $row) {
            $departmentName = $row['department'];
            $contractNumber = $row['contract'] ?? null;
            unset($row['department'], $row['contract']);

            $vehicle = Vehicle::updateOrCreate(
                ['plate_number' => $row['plate_number']],
                $row + [
                    'department_id' => $departments[$departmentName] ?? null,
                    'rental_contract_id' => $contractNumber ? $contracts[$contractNumber] : null,
                    // Odometer awal diasumsikan 5.000 km sebelum pencatatan sistem.
                    'initial_odometer' => max(0, $row['current_odometer'] - 5000),
                    'transmission' => 'manual',
                ],
            );

            $vehicles[$vehicle->plate_number] = $vehicle;
        }

        return $vehicles;
    }

    private function seedVehicleDocuments(array $vehicles): void
    {
        foreach ($vehicles as $plate => $vehicle) {
            VehicleDocument::updateOrCreate(
                ['vehicle_id' => $vehicle->getKey(), 'document_type' => VehicleDocumentType::Stnk],
                [
                    'document_number' => 'STNK-'.str_replace(' ', '', $plate),
                    'issued_date' => Carbon::today()->subYears(2)->toDateString(),
                    'expiry_date' => Carbon::today()->addDays(rand(20, 400))->toDateString(),
                    'reminder_days' => 30,
                ],
            );

            VehicleDocument::updateOrCreate(
                ['vehicle_id' => $vehicle->getKey(), 'document_type' => VehicleDocumentType::PajakTahunan],
                [
                    'document_number' => 'PJK-'.str_replace(' ', '', $plate),
                    'expiry_date' => Carbon::today()->addDays(rand(10, 360))->toDateString(),
                    'reminder_days' => 30,
                ],
            );
        }

        // B 6678 RS sengaja dibuat mendekati kedaluwarsa untuk uji notifikasi.
        VehicleDocument::where('vehicle_id', $vehicles['B 6678 RS']->getKey())
            ->where('document_type', VehicleDocumentType::Stnk)
            ->update(['expiry_date' => Carbon::today()->addDays(30)->toDateString()]);
    }

    private function seedTollCards(array $vehicles): void
    {
        $cards = [
            ['card_number' => '4011567890123344', 'issuer' => 'Mandiri e-Toll', 'vehicle' => 'B 2087 KJ', 'balance' => 245000],
            ['card_number' => '6019002188347712', 'issuer' => 'BCA Flazz', 'vehicle' => 'B 5501 GA', 'balance' => 78000],
            ['card_number' => '4321998711226650', 'issuer' => 'BRI Brizzi', 'vehicle' => 'B 1123 EE', 'balance' => 512000],
            ['card_number' => '5500443277890091', 'issuer' => 'BNI TapCash', 'vehicle' => 'B 9012 CD', 'balance' => 156000],
        ];

        $tollBalance = app(TollBalanceService::class);

        foreach ($cards as $card) {
            $vehiclePlate = $card['vehicle'];
            unset($card['vehicle']);

            $model = TollCard::updateOrCreate(
                ['card_number' => $card['card_number']],
                $card + [
                    'vehicle_id' => $vehicles[$vehiclePlate]->getKey(),
                    'min_balance_alert' => 100000,
                    'status' => TollCardStatus::Aktif,
                    'is_active' => true,
                ],
            );

            // Saldo awal dicatat sebagai top-up agar rumus BR-08 tetap konsisten.
            if ($model->topups()->doesntExist()) {
                $tollBalance->recordTopup($model, [
                    'topup_date' => Carbon::today()->subDays(20)->toDateString(),
                    'amount' => $card['balance'],
                    'method' => 'transfer',
                    'notes' => 'Saldo awal migrasi data.',
                ], \App\Models\User::first());
            }
        }
    }

    private function seedBookings(array $users, array $drivers, array $vehicles, $departments): void
    {
        $today = Carbon::today();

        $rows = [
            ['requester' => 'Dewi Anggraini', 'date' => $today->copy()->addDay(), 'destination' => 'Kantor Klien - Sudirman', 'purpose' => 'Presentasi proposal', 'duration' => DurationType::AntarJemput, 'odd_even' => true, 'urgent' => true, 'status' => BookingStatus::MenungguApproval],
            ['requester' => 'Budi Santoso', 'date' => $today->copy()->addDay(), 'destination' => 'Bandara Soekarno-Hatta', 'purpose' => 'Jemput tamu direksi', 'duration' => DurationType::AntarJemput, 'odd_even' => false, 'urgent' => false, 'status' => BookingStatus::MenungguApproval],
            ['requester' => 'Nabil Muthi Maulani', 'date' => $today->copy()->addDays(2), 'destination' => 'Data Center BSD', 'purpose' => 'Maintenance server', 'duration' => DurationType::SeharianStandby, 'odd_even' => false, 'urgent' => false, 'status' => BookingStatus::MenungguApproval],
            ['requester' => 'Siti Rahma', 'date' => $today->copy(), 'destination' => 'Bank Mandiri Pusat', 'purpose' => 'Setor & tarik dana operasional', 'duration' => DurationType::AntarJemput, 'odd_even' => false, 'urgent' => false, 'status' => BookingStatus::Disetujui, 'vehicle' => 'B 4456 ZP'],
            ['requester' => 'Agus Wijaya', 'date' => $today->copy(), 'destination' => 'Pameran JCC Senayan', 'purpose' => 'Booth produk & demo', 'duration' => DurationType::SeharianStandby, 'odd_even' => false, 'urgent' => false, 'status' => BookingStatus::SedangDigunakan, 'vehicle' => 'B 2087 KJ', 'driver' => 'Joko Wibowo', 'odometer_start' => 61800],
            ['requester' => 'Maya Puspita', 'date' => $today->copy(), 'destination' => 'Kementerian Perindustrian', 'purpose' => 'Rapat koordinasi', 'duration' => DurationType::SeharianStandby, 'odd_even' => false, 'urgent' => false, 'status' => BookingStatus::SedangDigunakan, 'vehicle' => 'B 5501 GA', 'driver' => 'Hendra Saputra', 'odometer_start' => 74050],
            ['requester' => 'Nina Kartika', 'date' => $today->copy()->subDay(), 'destination' => 'Gudang Cikarang', 'purpose' => 'Cek stok & pengiriman', 'duration' => DurationType::AntarJemput, 'odd_even' => false, 'urgent' => false, 'status' => BookingStatus::Selesai, 'vehicle' => 'B 7789 TN', 'driver' => 'Ahmad Fauzi', 'odometer_start' => 39880, 'odometer_end' => 40010],
            ['requester' => 'Dimas Prasetyo', 'date' => $today->copy()->subDays(2), 'destination' => 'Kantor Cabang Bekasi', 'purpose' => 'Instalasi perangkat', 'duration' => DurationType::AntarJemput, 'odd_even' => true, 'urgent' => false, 'status' => BookingStatus::Ditolak, 'reason' => 'Seluruh unit sudah teralokasi pada tanggal tersebut.'],
            ['requester' => 'Lestari Wulandari', 'date' => $today->copy()->subDays(3), 'destination' => 'Kantor Klien - Kuningan', 'purpose' => 'Meeting kontrak tahunan', 'duration' => DurationType::AntarJemput, 'odd_even' => false, 'urgent' => false, 'status' => BookingStatus::Dibatalkan],
        ];

        $adminGa = $users['Admin GA'];
        $sequence = 1;

        foreach ($rows as $row) {
            $date = $row['date'];
            $requester = $users[$row['requester']];

            $booking = Booking::updateOrCreate(
                ['booking_number' => sprintf('PJM/%s/%s/%03d', $date->format('Y'), $date->format('m'), $sequence++)],
                [
                    'requester_id' => $requester->getKey(),
                    'department_id' => $requester->department_id,
                    'booking_date' => $date->toDateString(),
                    'destination' => $row['destination'],
                    'purpose' => $row['purpose'],
                    'odd_even_zone' => $row['odd_even'],
                    'duration_type' => $row['duration'],
                    'is_urgent' => $row['urgent'],
                    'status' => $row['status'],
                    'vehicle_id' => isset($row['vehicle']) ? $vehicles[$row['vehicle']]->getKey() : null,
                    'driver_id' => isset($row['driver']) ? $drivers[$row['driver']]->getKey() : null,
                    'self_drive' => isset($row['vehicle']) && ! isset($row['driver']),
                    'approved_by' => isset($row['vehicle']) ? $adminGa->getKey() : null,
                    'approved_at' => isset($row['vehicle']) ? $date->copy()->subDay() : null,
                    'rejection_reason' => $row['reason'] ?? null,
                    'odometer_start' => $row['odometer_start'] ?? null,
                    'odometer_end' => $row['odometer_end'] ?? null,
                    'distance_traveled' => isset($row['odometer_end'])
                        ? $row['odometer_end'] - $row['odometer_start']
                        : null,
                    'actual_start_datetime' => isset($row['odometer_start']) ? $date->copy()->setTime(7, 30) : null,
                    'actual_end_datetime' => isset($row['odometer_end']) ? $date->copy()->setTime(17, 0) : null,
                    'created_by' => $requester->getKey(),
                ],
            );

            if ($row['status'] === BookingStatus::Dibatalkan) {
                $booking->forceFill([
                    'cancelled_at' => $date->copy()->subDay(),
                    'cancellation_reason' => 'Kegiatan ditunda oleh klien.',
                ])->save();
            }
        }
    }

    private function seedFuelTransactions(array $vehicles, array $drivers, array $users): void
    {
        $today = Carbon::today();

        $rows = [
            ['vehicle' => 'B 2087 KJ', 'driver' => 'Joko Wibowo', 'days_ago' => 0, 'hour' => 7, 'station' => 'SPBU Pertamina 34.123', 'liters' => 32.5, 'price' => 10000, 'full' => true, 'status' => FuelClaimStatus::Diajukan, 'odometer' => 61900],
            ['vehicle' => 'B 5501 GA', 'driver' => 'Hendra Saputra', 'days_ago' => 0, 'hour' => 6, 'station' => 'SPBU Shell Kuningan', 'liters' => 55.0, 'price' => 13700, 'full' => true, 'status' => FuelClaimStatus::Terverifikasi, 'odometer' => 74100],
            ['vehicle' => 'B 1123 EE', 'driver' => 'Ahmad Fauzi', 'days_ago' => 1, 'hour' => 16, 'station' => 'SPBU Pertamina 31.128', 'liters' => 28.0, 'price' => 10000, 'full' => false, 'status' => FuelClaimStatus::Terverifikasi, 'odometer' => 88600],
            ['vehicle' => 'B 7789 TN', 'driver' => 'Ahmad Fauzi', 'days_ago' => 1, 'hour' => 9, 'station' => 'SPBU Pertamina 34.150', 'liters' => 40.0, 'price' => 10000, 'full' => true, 'status' => FuelClaimStatus::Dibayar, 'odometer' => 39950],
            ['vehicle' => 'B 4456 ZP', 'driver' => 'Rudi Hartono', 'days_ago' => 2, 'hour' => 8, 'station' => 'SPBU Pertamina 31.101', 'liters' => 25.0, 'price' => 10000, 'full' => true, 'status' => FuelClaimStatus::Dibayar, 'odometer' => 31100],
            ['vehicle' => 'B 9012 CD', 'driver' => 'Rudi Hartono', 'days_ago' => 2, 'hour' => 14, 'station' => 'SPBU Shell Sudirman', 'liters' => 48.0, 'price' => 13700, 'full' => true, 'status' => FuelClaimStatus::Ditolak, 'odometer' => 18300, 'reason' => 'Nota tidak terbaca / duplikat nomor.'],
            ['vehicle' => 'B 3390 GG', 'driver' => 'Eko Prasetyo', 'days_ago' => 3, 'hour' => 11, 'station' => 'SPBU Pertamina 34.190', 'liters' => 60.0, 'price' => 10000, 'full' => true, 'status' => FuelClaimStatus::Terverifikasi, 'odometer' => 28800],
            ['vehicle' => 'B 8890 MK', 'driver' => 'Rudi Hartono', 'days_ago' => 4, 'hour' => 18, 'station' => 'SPBU BP-AKR Rasuna', 'liters' => 35.0, 'price' => 13700, 'full' => true, 'status' => FuelClaimStatus::Draft, 'odometer' => 11900],
            ['vehicle' => 'B 2087 KJ', 'driver' => 'Joko Wibowo', 'days_ago' => 6, 'hour' => 7, 'station' => 'SPBU Pertamina 34.123', 'liters' => 30.0, 'price' => 10000, 'full' => true, 'status' => FuelClaimStatus::Dibayar, 'odometer' => 61500],
            ['vehicle' => 'B 1123 EE', 'driver' => 'Ahmad Fauzi', 'days_ago' => 7, 'hour' => 15, 'station' => 'SPBU Pertamina 31.128', 'liters' => 27.5, 'price' => 10000, 'full' => false, 'status' => FuelClaimStatus::Dibayar, 'odometer' => 88200],
        ];

        $adminGa = $users['Admin GA'];
        $counter = 1;

        foreach ($rows as $row) {
            $driver = $drivers[$row['driver']];
            $datetime = $today->copy()->subDays($row['days_ago'])->setTime($row['hour'], 12);
            $total = $row['liters'] * $row['price'];

            $transaction = FuelTransaction::updateOrCreate(
                [
                    'station_name' => $row['station'],
                    'receipt_number' => 'NT-'.str_pad((string) $counter++, 5, '0', STR_PAD_LEFT),
                    'transaction_datetime' => $datetime,
                ],
                [
                    'vehicle_id' => $vehicles[$row['vehicle']]->getKey(),
                    'driver_id' => $driver->getKey(),
                    'claimant_id' => $driver->user_id,
                    'fuel_type' => $vehicles[$row['vehicle']]->fuel_type,
                    'liters' => $row['liters'],
                    'price_per_liter' => $row['price'],
                    'total_cost' => $total,
                    'approved_amount' => in_array($row['status'], [FuelClaimStatus::Terverifikasi, FuelClaimStatus::Dibayar], true) ? $total : null,
                    'payment_method' => PaymentMethod::Tunai,
                    'odometer' => $row['odometer'],
                    'is_full_tank' => $row['full'],
                    'receipt_photo_path' => 'demo/nota-contoh.jpg',
                    'status' => $row['status'],
                    'submitted_at' => $row['status'] === FuelClaimStatus::Draft ? null : $datetime->copy()->addHours(2),
                    'verified_by' => in_array($row['status'], [FuelClaimStatus::Terverifikasi, FuelClaimStatus::Dibayar, FuelClaimStatus::Ditolak], true) ? $adminGa->getKey() : null,
                    'verified_at' => in_array($row['status'], [FuelClaimStatus::Terverifikasi, FuelClaimStatus::Dibayar, FuelClaimStatus::Ditolak], true) ? $datetime->copy()->addDay() : null,
                    'rejection_reason' => $row['reason'] ?? null,
                    'created_by' => $driver->user_id,
                ],
            );
        }

        // BR-05 & FR-M3-05 — konsumsi full-to-full dan deteksi anomali baru
        // dihitung setelah seluruh transaksi ada, karena keduanya bergantung
        // pada pengisian penuh sebelum dan sesudahnya.
        $consumption = app(FuelConsumptionService::class);
        $anomalyDetector = app(FuelAnomalyDetector::class);

        foreach ($vehicles as $vehicle) {
            $consumption->recalculateVehicle($vehicle);
        }

        foreach (FuelTransaction::with('vehicle')->get() as $transaction) {
            $anomalyDetector->evaluate($transaction);
        }
    }

    private function seedTollTransactions(array $vehicles): void
    {
        $today = Carbon::today();
        $cards = TollCard::pluck('id', 'issuer');

        $rows = [
            ['days_ago' => 0, 'vehicle' => 'B 2087 KJ', 'card' => 'Mandiri e-Toll', 'entry' => 'Cikampek Utama', 'exit' => 'Dawuan', 'section' => 'Jakarta - Cikampek', 'amount' => 20000],
            ['days_ago' => 0, 'vehicle' => 'B 5501 GA', 'card' => 'BCA Flazz', 'entry' => 'Cawang', 'exit' => 'Halim', 'section' => 'Dalam Kota', 'amount' => 9500],
            ['days_ago' => 1, 'vehicle' => 'B 1123 EE', 'card' => 'BRI Brizzi', 'entry' => 'Pondok Gede Barat', 'exit' => 'Bekasi Barat', 'section' => 'Jakarta - Cikampek', 'amount' => 12000],
            ['days_ago' => 1, 'vehicle' => 'B 9012 CD', 'card' => 'BNI TapCash', 'entry' => 'Semanggi', 'exit' => 'Kebon Jeruk', 'section' => 'Dalam Kota', 'amount' => 9500],
            ['days_ago' => 2, 'vehicle' => 'B 2087 KJ', 'card' => 'Mandiri e-Toll', 'entry' => 'Dawuan', 'exit' => 'Cikampek Utama', 'section' => 'Jakarta - Cikampek', 'amount' => 20000],
        ];

        foreach ($rows as $row) {
            TollTransaction::updateOrCreate(
                [
                    'toll_card_id' => $cards[$row['card']],
                    'transaction_datetime' => $today->copy()->subDays($row['days_ago'])->setTime(7, 40),
                    'entry_gate' => $row['entry'],
                ],
                [
                    'vehicle_id' => $vehicles[$row['vehicle']]->getKey(),
                    'exit_gate' => $row['exit'],
                    'road_section' => $row['section'],
                    'vehicle_class' => VehicleClass::I,
                    'amount' => $row['amount'],
                ],
            );
        }

        $tollBalance = app(TollBalanceService::class);

        foreach (TollCard::all() as $card) {
            $tollBalance->recalculate($card);
        }
    }

    private function seedServiceSchedules(array $vehicles): void
    {
        $schedules = app(ServiceScheduleService::class);

        foreach ($vehicles as $vehicle) {
            $schedules->bootstrapForVehicle($vehicle);

            // Beri riwayat servis terakhir yang wajar (sekitar 35% interval
            // terpakai) supaya mayoritas unit berstatus Aman; tanpa ini
            // seluruh jadwal jatuh tempo sejak hari pertama.
            foreach ($vehicle->serviceSchedules()->get() as $schedule) {
                $interval = (int) $schedule->interval_km;

                if ($interval <= 0) {
                    continue;
                }

                $schedule->forceFill([
                    'last_service_odometer' => max(0, (int) $vehicle->current_odometer - (int) round($interval * 0.35)),
                    'last_service_date' => Carbon::today()->subMonths(2)->toDateString(),
                ])->save();

                $schedules->recalculate($schedule, $vehicle);
            }
        }

        // Sesuaikan beberapa unit agar dashboard servis memuat contoh
        // status Segera dan Jatuh Tempo (FR-M4-06).
        $adjustments = [
            'B 3345 QW' => ['Servis Ringan', 94020],   // sisa -320 km
            'B 2234 FF' => ['Ganti Oli Mesin', 56350], // sisa -150 km
            'B 6678 RS' => ['Ganti Ban', 112540],      // sisa 240 km
            'B 1123 EE' => ['Servis Ringan', 84450],   // sisa 700 km
        ];

        foreach ($adjustments as $plate => [$typeName, $lastOdometer]) {
            $vehicle = $vehicles[$plate];

            $schedule = $vehicle->serviceSchedules()
                ->whereHas('serviceType', fn ($q) => $q->where('name', $typeName))
                ->first();

            if ($schedule === null) {
                continue;
            }

            // Tanggal servis terakhir sengaja dibuat baru agar status
            // ditentukan oleh sisa KM (bukan interval waktu), sesuai contoh
            // perhitungan pada PRD §8.
            $schedule->forceFill([
                'last_service_odometer' => $lastOdometer,
                'last_service_date' => Carbon::today()->subMonth()->toDateString(),
            ])->save();

            $schedules->recalculate($schedule, $vehicle);
        }
    }
}
