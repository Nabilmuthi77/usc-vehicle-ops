<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

/** FR-M1-08 — nilai awal halaman pengaturan sistem. */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // --- Identitas perusahaan ---
            ['key' => 'company_name', 'value' => config('usc_vehicle_ops.company.name'), 'type' => 'string', 'group' => 'perusahaan', 'label' => 'Nama Perusahaan'],
            ['key' => 'company_address', 'value' => config('usc_vehicle_ops.company.address'), 'type' => 'string', 'group' => 'perusahaan', 'label' => 'Alamat Perusahaan'],
            ['key' => 'company_logo_path', 'value' => null, 'type' => 'string', 'group' => 'perusahaan', 'label' => 'Logo Perusahaan'],

            // --- Format penomoran dokumen ---
            ['key' => 'document_prefix_booking', 'value' => config('usc_vehicle_ops.numbering.booking'), 'type' => 'string', 'group' => 'penomoran', 'label' => 'Format Nomor Peminjaman'],
            ['key' => 'document_prefix_service_request', 'value' => config('usc_vehicle_ops.numbering.service_request'), 'type' => 'string', 'group' => 'penomoran', 'label' => 'Format Nomor Permintaan Servis'],
            ['key' => 'document_prefix_reimbursement_batch', 'value' => config('usc_vehicle_ops.numbering.reimbursement_batch'), 'type' => 'string', 'group' => 'penomoran', 'label' => 'Format Nomor Batch Reimbursement'],

            // --- Ambang batas operasional ---
            ['key' => 'service_threshold_km', 'value' => 1000, 'type' => 'integer', 'group' => 'servis', 'label' => 'Ambang Notifikasi Servis (km)', 'description' => 'FR-M4-04 — status "Segera Servis" saat sisa KM di bawah nilai ini.'],
            ['key' => 'daily_distance_threshold_km', 'value' => 1000, 'type' => 'integer', 'group' => 'peminjaman', 'label' => 'Ambang Jarak Wajar (km/hari)', 'description' => 'FR-M2-23 — peringatan bila selisih odometer melebihi nilai ini.'],
            ['key' => 'booking_lead_days', 'value' => 1, 'type' => 'integer', 'group' => 'peminjaman', 'label' => 'Minimal Pengajuan (H-)', 'description' => 'FR-M2-06 — pengajuan lebih mendadak ditandai urgent.'],
            ['key' => 'claim_deadline_days', 'value' => 30, 'type' => 'integer', 'group' => 'bbm', 'label' => 'Batas Waktu Klaim BBM (hari)', 'description' => 'FR-M3-14 — klaim melewati batas memerlukan persetujuan khusus.'],
            ['key' => 'consumption_deviation_percent', 'value' => 30, 'type' => 'integer', 'group' => 'bbm', 'label' => 'Ambang Anomali Konsumsi (%)'],
            ['key' => 'refuel_min_gap_hours', 'value' => 2, 'type' => 'integer', 'group' => 'bbm', 'label' => 'Jarak Minimal Antar Pengisian (jam)'],
            ['key' => 'document_reminder_days', 'value' => 30, 'type' => 'integer', 'group' => 'dokumen', 'label' => 'Pengingat Dokumen (H-)'],
            ['key' => 'rental_contract_reminder_days', 'value' => 60, 'type' => 'integer', 'group' => 'servis', 'label' => 'Pengingat Kontrak Sewa (H-)'],
            ['key' => 'vendor_escalation_days', 'value' => 3, 'type' => 'integer', 'group' => 'servis', 'label' => 'Eskalasi Vendor (hari kerja)'],
            ['key' => 'toll_min_balance', 'value' => 100000, 'type' => 'decimal', 'group' => 'tol', 'label' => 'Saldo Minimum e-Toll (Rp)'],
            ['key' => 'usage_average_days', 'value' => 30, 'type' => 'integer', 'group' => 'servis', 'label' => 'Periode Rata-rata Pemakaian (hari)'],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => Setting::castForStorage($setting['value'], $setting['type']),
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'label' => $setting['label'],
                    'description' => $setting['description'] ?? null,
                ],
            );
        }

        app(SettingService::class)->flush();
    }
}
