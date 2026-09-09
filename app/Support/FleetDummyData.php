<?php

namespace App\Support;

/**
 * Sumber data contoh (dummy) untuk tahap frontend.
 *
 * Seluruh data di sini bersifat statis dan HANYA dipakai untuk membangun
 * tampilan (UI) sebelum modul backend (model, migration, service layer)
 * dikerjakan. Ganti setiap pemanggilan class ini dengan query Eloquent
 * yang sesuai pada tahap implementasi backend.
 */
class FleetDummyData
{
    /**
     * Ekstrak digit terakhir nomor polisi -> paritas ganjil/genap.
     * Mengacu FR-M1-11 & BR-18.
     */
    public static function platParity(string $plateNumber): string
    {
        preg_match_all('/\d/', $plateNumber, $matches);
        $digits = $matches[0] ?? [];

        if (empty($digits)) {
            return '-';
        }

        $lastDigit = (int) end($digits);

        return $lastDigit % 2 === 0 ? 'Genap' : 'Ganjil';
    }

    public static function vehicles(): array
    {
        $vehicles = [
            ['plate' => 'B 1234 XI', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2022, 'ownership' => 'milik', 'department' => 'GA & Umum', 'status' => 'tersedia', 'odometer' => 45230, 'fuel' => 'Pertalite'],
            ['plate' => 'B 2087 KJ', 'brand' => 'Daihatsu', 'model' => 'Xenia', 'year' => 2021, 'ownership' => 'milik', 'department' => 'Marketing', 'status' => 'dipinjam', 'odometer' => 62110, 'fuel' => 'Pertalite'],
            ['plate' => 'B 9012 CD', 'brand' => 'Toyota', 'model' => 'Innova', 'year' => 2023, 'ownership' => 'sewa', 'department' => 'Direksi', 'status' => 'tersedia', 'odometer' => 18450, 'fuel' => 'Pertamax'],
            ['plate' => 'B 3345 QW', 'brand' => 'Mitsubishi', 'model' => 'Colt Diesel', 'year' => 2020, 'ownership' => 'milik', 'department' => 'Logistik', 'status' => 'servis', 'odometer' => 98700, 'fuel' => 'Solar'],
            ['plate' => 'B 4456 ZP', 'brand' => 'Honda', 'model' => 'Brio', 'year' => 2022, 'ownership' => 'milik', 'department' => 'Finance', 'status' => 'tersedia', 'odometer' => 31220, 'fuel' => 'Pertalite'],
            ['plate' => 'B 5501 GA', 'brand' => 'Toyota', 'model' => 'Hiace', 'year' => 2021, 'ownership' => 'leasing', 'department' => 'GA & Umum', 'status' => 'dipinjam', 'odometer' => 74300, 'fuel' => 'Solar'],
            ['plate' => 'B 6678 RS', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2019, 'ownership' => 'milik', 'department' => 'Marketing', 'status' => 'nonaktif', 'odometer' => 152300, 'fuel' => 'Pertalite'],
            ['plate' => 'B 7789 TN', 'brand' => 'Daihatsu', 'model' => 'Grand Max Box', 'year' => 2022, 'ownership' => 'sewa', 'department' => 'Logistik', 'status' => 'tersedia', 'odometer' => 40010, 'fuel' => 'Pertalite'],
            ['plate' => 'B 8890 MK', 'brand' => 'Honda', 'model' => 'CR-V', 'year' => 2023, 'ownership' => 'milik', 'department' => 'Direksi', 'status' => 'tersedia', 'odometer' => 12040, 'fuel' => 'Pertamax'],
            ['plate' => 'B 1123 EE', 'brand' => 'Toyota', 'model' => 'Avanza', 'year' => 2020, 'ownership' => 'milik', 'department' => 'HRD', 'status' => 'dipinjam', 'odometer' => 88750, 'fuel' => 'Pertalite'],
            ['plate' => 'B 2234 FF', 'brand' => 'Isuzu', 'model' => 'Elf Bus', 'year' => 2021, 'ownership' => 'leasing', 'department' => 'GA & Umum', 'status' => 'servis', 'odometer' => 66500, 'fuel' => 'Solar'],
            ['plate' => 'B 3390 GG', 'brand' => 'Suzuki', 'model' => 'Ertiga', 'year' => 2022, 'ownership' => 'milik', 'department' => 'IT', 'status' => 'tersedia', 'odometer' => 28900, 'fuel' => 'Pertalite'],
        ];

        foreach ($vehicles as $i => &$v) {
            $v['id'] = $i + 1;
            $v['parity'] = self::platParity($v['plate']);
        }

        return $vehicles;
    }

    public static function bookings(): array
    {
        return [
            ['no' => 'PJM/2026/08/041', 'requester' => 'Dewi Anggraini', 'department' => 'Marketing', 'date' => '06-08-2026', 'destination' => 'Kantor Klien - Sudirman', 'purpose' => 'Presentasi proposal', 'duration' => 'antar_jemput', 'odd_even' => true, 'urgent' => true, 'status' => 'menunggu_approval', 'vehicle' => null, 'driver' => null],
            ['no' => 'PJM/2026/08/040', 'requester' => 'Budi Santoso', 'department' => 'GA & Umum', 'date' => '06-08-2026', 'destination' => 'Bandara Soekarno-Hatta', 'purpose' => 'Jemput tamu direksi', 'duration' => 'antar_jemput', 'odd_even' => false, 'urgent' => false, 'status' => 'menunggu_approval', 'vehicle' => null, 'driver' => null],
            ['no' => 'PJM/2026/08/039', 'requester' => 'Rian Pratama', 'department' => 'IT', 'date' => '07-08-2026', 'destination' => 'Data Center BSD', 'purpose' => 'Maintenance server', 'duration' => 'seharian_standby', 'odd_even' => false, 'urgent' => false, 'status' => 'menunggu_approval', 'vehicle' => null, 'driver' => null],
            ['no' => 'PJM/2026/08/038', 'requester' => 'Siti Rahma', 'department' => 'Finance', 'date' => '05-08-2026', 'destination' => 'Bank Mandiri Pusat', 'purpose' => 'Setor & tarik dana operasional', 'duration' => 'antar_jemput', 'odd_even' => true, 'urgent' => false, 'status' => 'disetujui', 'vehicle' => 'B 4456 ZP', 'driver' => null],
            ['no' => 'PJM/2026/08/037', 'requester' => 'Agus Wijaya', 'department' => 'Marketing', 'date' => '05-08-2026', 'destination' => 'Pameran JCC Senayan', 'purpose' => 'Booth produk & demo', 'duration' => 'seharian_standby', 'odd_even' => false, 'urgent' => false, 'status' => 'sedang_digunakan', 'vehicle' => 'B 2087 KJ', 'driver' => 'Joko Wibowo'],
            ['no' => 'PJM/2026/08/036', 'requester' => 'Maya Puspita', 'department' => 'Direksi', 'date' => '05-08-2026', 'destination' => 'Kementerian Perindustrian', 'purpose' => 'Rapat koordinasi', 'duration' => 'seharian_standby', 'odd_even' => true, 'urgent' => false, 'status' => 'sedang_digunakan', 'vehicle' => 'B 5501 GA', 'driver' => 'Hendra Saputra'],
            ['no' => 'PJM/2026/08/035', 'requester' => 'Fajar Nugroho', 'department' => 'HRD', 'date' => '04-08-2026', 'destination' => 'Universitas Indonesia', 'purpose' => 'Rekrutmen kampus', 'duration' => 'seharian_standby', 'odd_even' => false, 'urgent' => false, 'status' => 'selesai', 'vehicle' => 'B 1123 EE', 'driver' => null],
            ['no' => 'PJM/2026/08/034', 'requester' => 'Nina Kartika', 'department' => 'Logistik', 'date' => '04-08-2026', 'destination' => 'Gudang Cikarang', 'purpose' => 'Cek stok & pengiriman', 'duration' => 'antar_jemput', 'odd_even' => false, 'urgent' => false, 'status' => 'selesai', 'vehicle' => 'B 7789 TN', 'driver' => 'Ahmad Fauzi'],
            ['no' => 'PJM/2026/08/033', 'requester' => 'Dimas Prasetyo', 'department' => 'IT', 'date' => '03-08-2026', 'destination' => 'Kantor Cabang Bekasi', 'purpose' => 'Instalasi perangkat', 'duration' => 'antar_jemput', 'odd_even' => true, 'urgent' => false, 'status' => 'ditolak', 'vehicle' => null, 'driver' => null, 'reason' => 'Seluruh unit sudah teralokasi pada tanggal tersebut'],
            ['no' => 'PJM/2026/08/032', 'requester' => 'Lestari Wulandari', 'department' => 'Marketing', 'date' => '02-08-2026', 'destination' => 'Kantor Klien - Kuningan', 'purpose' => 'Meeting kontrak tahunan', 'duration' => 'antar_jemput', 'odd_even' => false, 'urgent' => false, 'status' => 'dibatalkan', 'vehicle' => null, 'driver' => null],
        ];
    }

    public static function fuelTransactions(): array
    {
        return [
            ['id' => 1, 'vehicle' => 'B 2087 KJ', 'driver' => 'Joko Wibowo', 'datetime' => '05-08-2026 07:12', 'station' => 'SPBU Pertamina 34.123', 'liters' => 32.5, 'price' => 10000, 'total' => 325000, 'full' => true, 'status' => 'diajukan'],
            ['id' => 2, 'vehicle' => 'B 5501 GA', 'driver' => 'Hendra Saputra', 'datetime' => '05-08-2026 06:45', 'station' => 'SPBU Shell Kuningan', 'liters' => 55.0, 'price' => 13700, 'total' => 753500, 'full' => true, 'status' => 'terverifikasi'],
            ['id' => 3, 'vehicle' => 'B 1123 EE', 'driver' => 'Ahmad Fauzi', 'datetime' => '04-08-2026 16:30', 'station' => 'SPBU Pertamina 31.128', 'liters' => 28.0, 'price' => 10000, 'total' => 280000, 'full' => false, 'status' => 'terverifikasi'],
            ['id' => 4, 'vehicle' => 'B 7789 TN', 'driver' => 'Ahmad Fauzi', 'datetime' => '04-08-2026 09:05', 'station' => 'SPBU Pertamina 34.150', 'liters' => 40.0, 'price' => 10000, 'total' => 400000, 'full' => true, 'status' => 'dibayar'],
            ['id' => 5, 'vehicle' => 'B 4456 ZP', 'driver' => 'Siti Rahma', 'datetime' => '03-08-2026 08:20', 'station' => 'SPBU Pertamina 31.101', 'liters' => 25.0, 'price' => 10000, 'total' => 250000, 'full' => true, 'status' => 'dibayar'],
            ['id' => 6, 'vehicle' => 'B 9012 CD', 'driver' => 'Rudi Hartono', 'datetime' => '03-08-2026 14:10', 'station' => 'SPBU Shell Sudirman', 'liters' => 48.0, 'price' => 13700, 'total' => 657600, 'full' => true, 'status' => 'ditolak', 'note' => 'Nota tidak terbaca / duplikat nomor'],
            ['id' => 7, 'vehicle' => 'B 3390 GG', 'driver' => 'Eko Prasetyo', 'datetime' => '02-08-2026 11:00', 'station' => 'SPBU Pertamina 34.190', 'liters' => 60.0, 'price' => 10000, 'total' => 600000, 'full' => true, 'status' => 'terverifikasi', 'anomaly' => 'Liter melebihi kapasitas tangki'],
            ['id' => 8, 'vehicle' => 'B 8890 MK', 'driver' => 'Rudi Hartono', 'datetime' => '01-08-2026 18:40', 'station' => 'SPBU BP-AKR Rasuna', 'liters' => 35.0, 'price' => 13700, 'total' => 479500, 'full' => true, 'status' => 'draft'],
            ['id' => 9, 'vehicle' => 'B 2087 KJ', 'driver' => 'Joko Wibowo', 'datetime' => '31-07-2026 07:30', 'station' => 'SPBU Pertamina 34.123', 'liters' => 30.0, 'price' => 10000, 'total' => 300000, 'full' => true, 'status' => 'dibayar'],
            ['id' => 10, 'vehicle' => 'B 1123 EE', 'driver' => 'Ahmad Fauzi', 'datetime' => '30-07-2026 15:55', 'station' => 'SPBU Pertamina 31.128', 'liters' => 27.5, 'price' => 10000, 'total' => 275000, 'full' => false, 'status' => 'dibayar'],
        ];
    }

    public static function serviceSchedules(): array
    {
        return [
            ['vehicle' => 'B 3345 QW', 'type' => 'Servis Ringan', 'interval_km' => 5000, 'remaining_km' => -320, 'status' => 'jatuh_tempo', 'ownership' => 'milik'],
            ['vehicle' => 'B 2234 FF', 'type' => 'Ganti Oli Mesin', 'interval_km' => 10000, 'remaining_km' => -150, 'status' => 'jatuh_tempo', 'ownership' => 'leasing'],
            ['vehicle' => 'B 6678 RS', 'type' => 'Ganti Ban', 'interval_km' => 40000, 'remaining_km' => 240, 'status' => 'segera', 'ownership' => 'milik'],
            ['vehicle' => 'B 1123 EE', 'type' => 'Servis Ringan', 'interval_km' => 5000, 'remaining_km' => 700, 'status' => 'segera', 'ownership' => 'milik'],
            ['vehicle' => 'B 5501 GA', 'type' => 'Ganti Filter Udara', 'interval_km' => 20000, 'remaining_km' => 850, 'status' => 'segera', 'ownership' => 'leasing'],
            ['vehicle' => 'B 2087 KJ', 'type' => 'Ganti Oli Mesin', 'interval_km' => 10000, 'remaining_km' => 2340, 'status' => 'aman', 'ownership' => 'milik'],
            ['vehicle' => 'B 9012 CD', 'type' => 'Servis Ringan', 'interval_km' => 5000, 'remaining_km' => 3550, 'status' => 'aman', 'ownership' => 'sewa'],
            ['vehicle' => 'B 4456 ZP', 'type' => 'Servis Ringan', 'interval_km' => 5000, 'remaining_km' => 4780, 'status' => 'aman', 'ownership' => 'milik'],
            ['vehicle' => 'B 7789 TN', 'type' => 'Ganti Filter Udara', 'interval_km' => 20000, 'remaining_km' => 5990, 'status' => 'aman', 'ownership' => 'sewa'],
            ['vehicle' => 'B 3390 GG', 'type' => 'Ganti Oli Mesin', 'interval_km' => 10000, 'remaining_km' => 7100, 'status' => 'aman', 'ownership' => 'milik'],
        ];
    }

    public static function tollCards(): array
    {
        return [
            ['card_number' => '4011 5678 9012 3344', 'issuer' => 'Mandiri e-Toll', 'vehicle' => 'B 2087 KJ', 'balance' => 245000, 'min_alert' => 100000],
            ['card_number' => '6019 0021 8834 7712', 'issuer' => 'BCA Flazz', 'vehicle' => 'B 5501 GA', 'balance' => 78000, 'min_alert' => 100000],
            ['card_number' => '4321 9987 1122 6650', 'issuer' => 'BRI Brizzi', 'vehicle' => 'B 1123 EE', 'balance' => 512000, 'min_alert' => 100000],
            ['card_number' => '5500 4432 7789 0091', 'issuer' => 'BNI TapCash', 'vehicle' => 'B 9012 CD', 'balance' => 156000, 'min_alert' => 100000],
        ];
    }

    public static function tollTransactions(): array
    {
        return [
            ['datetime' => '05-08-2026 07:40', 'vehicle' => 'B 2087 KJ', 'card' => 'Mandiri e-Toll', 'entry' => 'Cikampek Utama', 'exit' => 'Dawuan', 'section' => 'Jakarta - Cikampek', 'class' => 'I', 'amount' => 20000],
            ['datetime' => '05-08-2026 06:55', 'vehicle' => 'B 5501 GA', 'card' => 'BCA Flazz', 'entry' => 'Cawang', 'exit' => 'Halim', 'section' => 'Dalam Kota', 'class' => 'I', 'amount' => 9500],
            ['datetime' => '04-08-2026 17:10', 'vehicle' => 'B 1123 EE', 'card' => 'BRI Brizzi', 'entry' => 'Pondok Gede Barat', 'exit' => 'Bekasi Barat', 'section' => 'Jakarta - Cikampek', 'class' => 'I', 'amount' => 12000],
            ['datetime' => '04-08-2026 09:20', 'vehicle' => 'B 9012 CD', 'card' => 'BNI TapCash', 'entry' => 'Semanggi', 'exit' => 'Kebon Jeruk', 'section' => 'Dalam Kota', 'class' => 'I', 'amount' => 9500],
            ['datetime' => '03-08-2026 15:05', 'vehicle' => 'B 2087 KJ', 'card' => 'Mandiri e-Toll', 'entry' => 'Dawuan', 'exit' => 'Cikampek Utama', 'section' => 'Jakarta - Cikampek', 'class' => 'I', 'amount' => 20000],
            ['datetime' => '02-08-2026 08:00', 'vehicle' => 'B 7789 TN', 'card' => 'Mandiri e-Toll', 'entry' => 'Cibitung', 'exit' => 'Cikarang Barat', 'section' => 'Jakarta - Cikampek', 'class' => 'II', 'amount' => 15500],
        ];
    }

    public static function dashboardStats(): array
    {
        $vehicles = self::vehicles();

        return [
            'total_kendaraan' => count($vehicles),
            'tersedia' => count(array_filter($vehicles, fn ($v) => $v['status'] === 'tersedia')),
            'dipinjam' => count(array_filter($vehicles, fn ($v) => $v['status'] === 'dipinjam')),
            'servis' => count(array_filter($vehicles, fn ($v) => $v['status'] === 'servis')),
            'menunggu_approval' => count(array_filter(self::bookings(), fn ($b) => $b['status'] === 'menunggu_approval')),
            'servis_jatuh_tempo' => count(array_filter(self::serviceSchedules(), fn ($s) => $s['status'] === 'jatuh_tempo')),
            'biaya_bulan_ini' => 48750000,
        ];
    }

    /** 12 bulan terakhir, dalam juta rupiah, urutan [bbm, tol, servis]. */
    public static function monthlyCostTrend(): array
    {
        $months = ['Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'];
        $bbm = [18.2, 19.4, 21.1, 20.6, 17.8, 22.3, 23.0, 21.7, 24.1, 22.9, 25.4, 26.2];
        $tol = [4.1, 4.6, 5.0, 5.8, 4.4, 5.2, 5.6, 5.1, 6.0, 5.8, 6.4, 6.7];
        $servis = [6.5, 3.2, 8.9, 4.1, 12.4, 5.6, 3.8, 9.2, 4.6, 7.1, 5.3, 15.85];

        return compact('months', 'bbm', 'tol', 'servis');
    }

    public static function topCostlyVehicles(): array
    {
        return [
            ['vehicle' => 'B 3345 QW', 'total' => 14250000],
            ['vehicle' => 'B 5501 GA', 'total' => 11080000],
            ['vehicle' => 'B 2234 FF', 'total' => 9640000],
            ['vehicle' => 'B 2087 KJ', 'total' => 7320000],
            ['vehicle' => 'B 6678 RS', 'total' => 6210000],
        ];
    }

    public static function utilizationTop(): array
    {
        return [
            ['vehicle' => 'B 2087 KJ', 'percent' => 82],
            ['vehicle' => 'B 5501 GA', 'percent' => 76],
            ['vehicle' => 'B 1123 EE', 'percent' => 68],
            ['vehicle' => 'B 7789 TN', 'percent' => 54],
            ['vehicle' => 'B 9012 CD', 'percent' => 41],
        ];
    }

    public static function drivers(): array
    {
        return [
            ['id' => 1, 'name' => 'Joko Wibowo', 'employee_id' => 'DRV-001', 'license_number' => '5301021107850001', 'license_type' => 'SIM B1 Umum', 'license_expiry' => '14-02-2027', 'phone' => '0812-3456-7801', 'is_active' => true],
            ['id' => 2, 'name' => 'Hendra Saputra', 'employee_id' => 'DRV-002', 'license_number' => '5301021107880002', 'license_type' => 'SIM B2 Umum', 'license_expiry' => '02-09-2026', 'phone' => '0812-3456-7802', 'is_active' => true],
            ['id' => 3, 'name' => 'Ahmad Fauzi', 'employee_id' => 'DRV-003', 'license_number' => '5301021107900003', 'license_type' => 'SIM A Umum', 'license_expiry' => '28-08-2026', 'phone' => '0812-3456-7803', 'is_active' => true],
            ['id' => 4, 'name' => 'Rudi Hartono', 'employee_id' => 'DRV-004', 'license_number' => '5301021107820004', 'license_type' => 'SIM B1 Umum', 'license_expiry' => '19-12-2026', 'phone' => '0812-3456-7804', 'is_active' => true],
            ['id' => 5, 'name' => 'Eko Prasetyo', 'employee_id' => 'DRV-005', 'license_number' => '5301021107930005', 'license_type' => 'SIM A Umum', 'license_expiry' => '05-03-2027', 'phone' => '0812-3456-7805', 'is_active' => true],
            ['id' => 6, 'name' => 'Wahyu Setiawan', 'employee_id' => 'DRV-006', 'license_number' => '5301021107870006', 'license_type' => 'SIM B2 Umum', 'license_expiry' => '30-08-2026', 'phone' => '0812-3456-7806', 'is_active' => true],
            ['id' => 7, 'name' => 'Bambang Irawan', 'employee_id' => 'DRV-007', 'license_number' => '5301021107790007', 'license_type' => 'SIM B1 Umum', 'license_expiry' => '11-01-2026', 'phone' => '0812-3456-7807', 'is_active' => false],
            ['id' => 8, 'name' => 'Dedi Kurniawan', 'employee_id' => 'DRV-008', 'license_number' => '5301021107910008', 'license_type' => 'SIM A Umum', 'license_expiry' => '22-10-2026', 'phone' => '0812-3456-7808', 'is_active' => true],
        ];
    }

    public static function departments(): array
    {
        return [
            ['id' => 1, 'name' => 'GA & Umum', 'code' => 'GA', 'pic' => 'Budi Santoso', 'vehicle_count' => 3, 'user_count' => 12],
            ['id' => 2, 'name' => 'Marketing', 'code' => 'MKT', 'pic' => 'Agus Wijaya', 'vehicle_count' => 2, 'user_count' => 18],
            ['id' => 3, 'name' => 'Direksi', 'code' => 'DIR', 'pic' => 'Maya Puspita', 'vehicle_count' => 2, 'user_count' => 4],
            ['id' => 4, 'name' => 'Logistik', 'code' => 'LOG', 'pic' => 'Nina Kartika', 'vehicle_count' => 2, 'user_count' => 9],
            ['id' => 5, 'name' => 'Finance', 'code' => 'FIN', 'pic' => 'Siti Rahma', 'vehicle_count' => 1, 'user_count' => 7],
            ['id' => 6, 'name' => 'HRD', 'code' => 'HRD', 'pic' => 'Fajar Nugroho', 'vehicle_count' => 1, 'user_count' => 6],
            ['id' => 7, 'name' => 'IT', 'code' => 'IT', 'pic' => 'Rian Pratama', 'vehicle_count' => 1, 'user_count' => 8],
        ];
    }

    public static function vendors(): array
    {
        return [
            ['id' => 1, 'name' => 'Bengkel Resmi Auto2000', 'type' => 'bengkel', 'pic' => 'Pak Slamet', 'phone' => '021-5551234', 'address' => 'Jl. MT Haryono No. 8, Jakarta', 'is_active' => true],
            ['id' => 2, 'name' => 'Bengkel Isuzu Astra', 'type' => 'bengkel', 'pic' => 'Bu Anita', 'phone' => '021-5559876', 'address' => 'Jl. Yos Sudarso No. 21, Jakarta', 'is_active' => true],
            ['id' => 3, 'name' => 'SPBU Pertamina 34.123', 'type' => 'spbu', 'pic' => 'Pak Herman', 'phone' => '021-5552211', 'address' => 'Jl. Gatot Subroto, Jakarta', 'is_active' => true],
            ['id' => 4, 'name' => 'SPBU Shell Kuningan', 'type' => 'spbu', 'pic' => 'Bu Ratna', 'phone' => '021-5553322', 'address' => 'Jl. HR Rasuna Said, Jakarta', 'is_active' => true],
            ['id' => 5, 'name' => 'CV Sewa Armada Jaya', 'type' => 'leasing', 'pic' => 'Pak Yusuf', 'phone' => '021-5554455', 'address' => 'Jl. Casablanca Raya No. 10, Jakarta', 'is_active' => true],
            ['id' => 6, 'name' => 'PT Trac Astra Rent', 'type' => 'leasing', 'pic' => 'Bu Melati', 'phone' => '021-5556677', 'address' => 'Jl. TB Simatupang, Jakarta', 'is_active' => true],
            ['id' => 7, 'name' => 'Bengkel Honda Cilandak', 'type' => 'bengkel', 'pic' => 'Pak Rio', 'phone' => '021-5558899', 'address' => 'Jl. Cilandak KKO, Jakarta', 'is_active' => false],
        ];
    }

    public static function rentalContracts(): array
    {
        return [
            ['vendor' => 'CV Sewa Armada Jaya', 'vehicle' => 'B 9012 CD', 'contract_number' => 'KTR/2025/014', 'start' => '01-09-2025', 'end' => '31-08-2026', 'monthly_cost' => 8500000, 'days_remaining' => 26],
            ['vendor' => 'PT Trac Astra Rent', 'vehicle' => 'B 5501 GA', 'contract_number' => 'KTR/2024/031', 'start' => '01-01-2025', 'end' => '31-12-2026', 'monthly_cost' => 11500000, 'days_remaining' => 148],
            ['vendor' => 'PT Trac Astra Rent', 'vehicle' => 'B 2234 FF', 'contract_number' => 'KTR/2025/002', 'start' => '15-01-2025', 'end' => '14-01-2027', 'monthly_cost' => 13200000, 'days_remaining' => 162],
            ['vendor' => 'CV Sewa Armada Jaya', 'vehicle' => 'B 7789 TN', 'contract_number' => 'KTR/2025/019', 'start' => '01-03-2025', 'end' => '28-02-2026', 'monthly_cost' => 6200000, 'days_remaining' => -158],
        ];
    }

    public static function users(): array
    {
        return [
            ['id' => 1, 'name' => 'Admin GA', 'email' => 'admin@usc_vehicle_ops.local', 'department' => 'GA & Umum', 'role' => 'Admin GA', 'is_active' => true, 'last_login' => '05-08-2026 08:12'],
            ['id' => 2, 'name' => 'Slamet Riyadi', 'email' => 'slamet.riyadi@company.co.id', 'department' => 'GA & Umum', 'role' => 'Admin Sistem', 'is_active' => true, 'last_login' => '05-08-2026 07:45'],
            ['id' => 3, 'name' => 'Dewi Anggraini', 'email' => 'dewi.anggraini@company.co.id', 'department' => 'Marketing', 'role' => 'Karyawan', 'is_active' => true, 'last_login' => '04-08-2026 16:20'],
            ['id' => 4, 'name' => 'Rian Pratama', 'email' => 'rian.pratama@company.co.id', 'department' => 'IT', 'role' => 'Karyawan', 'is_active' => true, 'last_login' => '04-08-2026 14:02'],
            ['id' => 5, 'name' => 'Joko Wibowo', 'email' => 'joko.wibowo@company.co.id', 'department' => 'GA & Umum', 'role' => 'Driver', 'is_active' => true, 'last_login' => '05-08-2026 07:05'],
            ['id' => 6, 'name' => 'Hendra Saputra', 'email' => 'hendra.saputra@company.co.id', 'department' => 'GA & Umum', 'role' => 'Driver', 'is_active' => true, 'last_login' => '05-08-2026 06:40'],
            ['id' => 7, 'name' => 'Maya Puspita', 'email' => 'maya.puspita@company.co.id', 'department' => 'Direksi', 'role' => 'Viewer', 'is_active' => true, 'last_login' => '03-08-2026 09:30'],
            ['id' => 8, 'name' => 'Siti Rahma', 'email' => 'siti.rahma@company.co.id', 'department' => 'Finance', 'role' => 'Viewer', 'is_active' => true, 'last_login' => '02-08-2026 11:15'],
            ['id' => 9, 'name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@company.co.id', 'department' => 'HRD', 'role' => 'Karyawan', 'is_active' => false, 'last_login' => '20-06-2026 10:00'],
        ];
    }

    public static function roles(): array
    {
        return [
            ['name' => 'Admin Sistem', 'description' => 'IT / pengelola aplikasi — kelola user, master data, konfigurasi sistem', 'count' => 1],
            ['name' => 'Admin GA', 'description' => 'Satu-satunya pemberi persetujuan peminjaman, verifikasi klaim BBM, input tol, request servis', 'count' => 1],
            ['name' => 'Karyawan', 'description' => 'Mengajukan peminjaman, melihat status, mengajukan klaim BBM', 'count' => 3],
            ['name' => 'Driver', 'description' => 'Input odometer, unggah nota BBM, catat transaksi tol', 'count' => 2],
            ['name' => 'Viewer', 'description' => 'Direksi / Finance — dashboard, laporan biaya, proses reimbursement (read-only)', 'count' => 2],
        ];
    }

    /** Laporan biaya operasional gabungan per kendaraan (BR-09, BR-10). */
    public static function reportCostByVehicle(): array
    {
        $rows = [
            ['vehicle' => 'B 1234 XI', 'bbm' => 1850000, 'tol' => 320000, 'servis' => 0, 'km' => 1240],
            ['vehicle' => 'B 2087 KJ', 'bbm' => 3120000, 'tol' => 580000, 'servis' => 750000, 'km' => 2380],
            ['vehicle' => 'B 9012 CD', 'bbm' => 2450000, 'tol' => 410000, 'servis' => 0, 'km' => 1620],
            ['vehicle' => 'B 3345 QW', 'bbm' => 4200000, 'tol' => 150000, 'servis' => 9900000, 'km' => 1980],
            ['vehicle' => 'B 4456 ZP', 'bbm' => 980000, 'tol' => 90000, 'servis' => 0, 'km' => 860],
            ['vehicle' => 'B 5501 GA', 'bbm' => 5100000, 'tol' => 720000, 'servis' => 0, 'km' => 3140],
            ['vehicle' => 'B 6678 RS', 'bbm' => 610000, 'tol' => 60000, 'servis' => 5540000, 'km' => 420],
            ['vehicle' => 'B 7789 TN', 'bbm' => 2780000, 'tol' => 340000, 'servis' => 0, 'km' => 1960],
            ['vehicle' => 'B 8890 MK', 'bbm' => 1420000, 'tol' => 260000, 'servis' => 0, 'km' => 780],
            ['vehicle' => 'B 1123 EE', 'bbm' => 3340000, 'tol' => 480000, 'servis' => 0, 'km' => 2210],
            ['vehicle' => 'B 2234 FF', 'bbm' => 3980000, 'tol' => 210000, 'servis' => 0, 'km' => 1740],
            ['vehicle' => 'B 3390 GG', 'bbm' => 1120000, 'tol' => 140000, 'servis' => 0, 'km' => 940],
        ];

        foreach ($rows as &$r) {
            $r['total'] = $r['bbm'] + $r['tol'] + $r['servis'];
            $r['cost_per_km'] = $r['km'] > 0 ? round($r['total'] / $r['km']) : 0;
        }

        return $rows;
    }

    public static function reportUtilization(): array
    {
        return [
            ['vehicle' => 'B 2087 KJ', 'days_used' => 25, 'total_days' => 31, 'trips' => 18],
            ['vehicle' => 'B 5501 GA', 'days_used' => 23, 'total_days' => 31, 'trips' => 15],
            ['vehicle' => 'B 1123 EE', 'days_used' => 21, 'total_days' => 31, 'trips' => 14],
            ['vehicle' => 'B 7789 TN', 'days_used' => 17, 'total_days' => 31, 'trips' => 11],
            ['vehicle' => 'B 9012 CD', 'days_used' => 13, 'total_days' => 31, 'trips' => 9],
            ['vehicle' => 'B 2234 FF', 'days_used' => 12, 'total_days' => 31, 'trips' => 8],
            ['vehicle' => 'B 3390 GG', 'days_used' => 10, 'total_days' => 31, 'trips' => 7],
            ['vehicle' => 'B 8890 MK', 'days_used' => 8, 'total_days' => 31, 'trips' => 5],
            ['vehicle' => 'B 4456 ZP', 'days_used' => 7, 'total_days' => 31, 'trips' => 5],
            ['vehicle' => 'B 1234 XI', 'days_used' => 6, 'total_days' => 31, 'trips' => 4],
        ];
    }

    public static function reportDriverActivity(): array
    {
        return [
            ['driver' => 'Joko Wibowo', 'trips' => 18, 'km' => 2380, 'fuel_claims' => 4, 'avg_consumption' => 10.8],
            ['driver' => 'Hendra Saputra', 'trips' => 15, 'km' => 3140, 'fuel_claims' => 3, 'avg_consumption' => 8.4],
            ['driver' => 'Ahmad Fauzi', 'trips' => 14, 'km' => 2210, 'fuel_claims' => 5, 'avg_consumption' => 11.2],
            ['driver' => 'Rudi Hartono', 'trips' => 9, 'km' => 1620, 'fuel_claims' => 2, 'avg_consumption' => 9.6],
            ['driver' => 'Eko Prasetyo', 'trips' => 7, 'km' => 940, 'fuel_claims' => 2, 'avg_consumption' => 10.1],
            ['driver' => 'Wahyu Setiawan', 'trips' => 5, 'km' => 780, 'fuel_claims' => 1, 'avg_consumption' => 9.9],
        ];
    }

    /** Daftar notifikasi lengkap untuk halaman FR-M7-01 (dropdown topbar hanya menampilkan 4 teratas). */
    public static function allNotifications(): array
    {
        return [
            ['title' => 'Peminjaman baru menunggu approval', 'detail' => 'PJM/2026/08/041 — Dewi Anggraini mengajukan peminjaman untuk 06-08-2026', 'time' => '5 menit lalu', 'unread' => true, 'icon' => 'calendar', 'category' => 'Peminjaman'],
            ['title' => 'Servis jatuh tempo', 'detail' => 'B 3345 QW sudah melewati jadwal servis ringan sejauh 320 km', 'time' => '1 jam lalu', 'unread' => true, 'icon' => 'wrench', 'category' => 'Servis'],
            ['title' => 'Klaim BBM diajukan', 'detail' => 'Rudi Hartono mengajukan klaim BBM B 9012 CD sebesar Rp 657.600', 'time' => '3 jam lalu', 'unread' => true, 'icon' => 'droplet', 'category' => 'BBM'],
            ['title' => 'Dokumen akan kedaluwarsa', 'detail' => 'STNK B 6678 RS akan berakhir dalam 30 hari (04-09-2026)', 'time' => 'Kemarin', 'unread' => true, 'icon' => 'exclamation', 'category' => 'Dokumen'],
            ['title' => 'Peminjaman disetujui', 'detail' => 'PJM/2026/08/038 milik Siti Rahma disetujui, unit B 4456 ZP', 'time' => 'Kemarin', 'unread' => false, 'icon' => 'check-circle', 'category' => 'Peminjaman'],
            ['title' => 'Saldo kartu e-toll dibawah minimum saldo', 'detail' => 'Kartu BCA Flazz (B 5501 GA) tersisa Rp 78.000, di bawah ambang Rp 100.000', 'time' => '2 hari lalu', 'unread' => false, 'icon' => 'ticket', 'category' => 'Tol'],
            ['title' => 'Servis segera diperlukan', 'detail' => 'B 6678 RS mendekati jadwal ganti ban, sisa 240 km', 'time' => '2 hari lalu', 'unread' => false, 'icon' => 'wrench', 'category' => 'Servis'],
            ['title' => 'Kontrak sewa akan berakhir', 'detail' => 'Kontrak KTR/2025/014 (B 9012 CD) dengan CV Sewa Armada Jaya berakhir dalam 26 hari', 'time' => '3 hari lalu', 'unread' => false, 'icon' => 'briefcase', 'category' => 'Kontrak Sewa'],
            ['title' => 'Klaim BBM ditolak', 'detail' => 'Klaim B 9012 CD ditolak — nota tidak terbaca / duplikat nomor', 'time' => '3 hari lalu', 'unread' => false, 'icon' => 'droplet', 'category' => 'BBM'],
            ['title' => 'Peminjaman terlambat dikembalikan', 'detail' => 'PJM/2026/07/098 (B 2234 FF) belum dikembalikan melewati jadwal', 'time' => '4 hari lalu', 'unread' => false, 'icon' => 'exclamation', 'category' => 'Peminjaman'],
        ];
    }

    public static function settings(): array
    {
        return [
            'company_name' => 'PT USC Indonesia',
            'company_address' => 'Kawasan Industri Mitra Karawang: Jl. Miyata Raya Selatan II Blok F No. 1/07/08, Parungmulya, Kec. Ciampel, Karawang, Jawa Barat 41363',
            'document_prefix_booking' => 'PJM/{YYYY}/{MM}/{urut}',
            'document_prefix_service_request' => 'RSV/{YYYY}/{MM}/{urut}',
            'service_threshold_km' => 1000,
            'overdue_distance_threshold_km' => 1000,
            'claim_deadline_days' => 30,
            'document_reminder_days' => 30,
            'rental_contract_reminder_days' => 60,
            'vendor_escalation_days' => 3,
            'toll_min_balance' => 100000,
            'session_timeout_minutes' => 60,
            'fuel_prices' => [
                ['type' => 'Pertalite', 'price' => 10000],
                ['type' => 'Pertamax', 'price' => 13700],
                ['type' => 'Solar', 'price' => 6800],
                ['type' => 'Dexlite', 'price' => 14000],
            ],
        ];
    }
}
