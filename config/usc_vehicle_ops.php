<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi USC_VEHICLE_OPS
|--------------------------------------------------------------------------
|
| Nilai di sini adalah default bawaan aplikasi. Nilai yang sama dapat
| ditimpa lewat halaman Pengaturan (FR-M1-08) yang tersimpan di tabel
| `settings`; SettingService membaca database lebih dahulu lalu jatuh
| kembali ke nilai di berkas ini.
|
*/

return [

    'company' => [
        'name' => env('USC_VEHICLE_OPS_COMPANY_NAME', 'PT USC Indonesia'),
        'address' => env('USC_VEHICLE_OPS_COMPANY_ADDRESS', ''),
        'logo_path' => null,
    ],

    /*
    | Format penomoran dokumen. Placeholder yang dikenali:
    | {YYYY} tahun, {MM} bulan, {urut} nomor urut per bulan.
    */
    'numbering' => [
        'booking' => 'PJM/{YYYY}/{MM}/{urut}',
        'service_request' => 'RSV/{YYYY}/{MM}/{urut}',
        'reimbursement_batch' => 'RMB/{YYYY}/{MM}/{urut}',
        'sequence_padding' => 3,
    ],

    /*
    | Ambang batas & aturan bisnis yang dapat dikonfigurasi.
    */
    'thresholds' => [
        // FR-M4-04 — ambang status "Segera Servis" dalam kilometer.
        'service_km' => 1000,
        // FR-M2-23 — batas kewajaran jarak tempuh per hari.
        'daily_distance_km' => 1000,
        // FR-M3-14 — batas hari pengajuan klaim sejak tanggal nota.
        'claim_deadline_days' => 30,
        // FR-M1-03 — pengingat dokumen kendaraan akan kedaluwarsa.
        'document_reminder_days' => 30,
        // FR-M4-22 — pengingat kontrak sewa akan berakhir.
        'rental_contract_reminder_days' => 60,
        // FR-M4-20 — eskalasi permintaan servis yang belum direspons vendor.
        'vendor_escalation_days' => 3,
        // FR-M5-07 — ambang saldo minimum kartu e-toll.
        'toll_min_balance' => 100000,
        // FR-M2-06 — pengajuan minimal H-berapa sebelum tanggal pemakaian.
        'booking_lead_days' => 1,
        // FR-M3-05 — penyimpangan konsumsi yang dianggap anomali.
        'consumption_deviation_percent' => 30,
        // FR-M3-05 — jarak antar pengisian yang dianggap mencurigakan (jam).
        'refuel_min_gap_hours' => 2,
    ],

    /*
    | FR-M4-05 — jumlah hari ke belakang untuk menghitung rata-rata pemakaian
    | km/hari yang dipakai memperkirakan tanggal jatuh tempo servis.
    */
    'usage_average_days' => 30,

    /*
    | Batasan unggahan berkas (NFR Keamanan): maksimal 5 MB, jpg/png/pdf.
    */
    'uploads' => [
        'max_size_kb' => 5120,
        'image_mimes' => ['jpg', 'jpeg', 'png'],
        'document_mimes' => ['jpg', 'jpeg', 'png', 'pdf'],
        'disk' => 'public',
        // Kompresi foto agar penyimpanan tidak cepat penuh (§14 Risiko).
        'image_max_width' => 1600,
        'image_quality' => 75,
    ],

];
