<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * FR-M1-08 — akses terpusat ke pengaturan sistem.
 *
 * Nilai dibaca dari tabel `settings`; bila belum ada barisnya, nilai jatuh
 * kembali ke `config/usc_vehicle_ops.php` sehingga aplikasi tetap berjalan pada
 * instalasi baru sebelum seeder dijalankan.
 */
class SettingService
{
    public const CACHE_KEY = 'usc_vehicle_ops.settings';

    /** Pemetaan kunci pengaturan ke jalur config bawaan. */
    private const CONFIG_FALLBACK = [
        'company_name' => 'usc_vehicle_ops.company.name',
        'company_address' => 'usc_vehicle_ops.company.address',
        'company_logo_path' => 'usc_vehicle_ops.company.logo_path',
        'document_prefix_booking' => 'usc_vehicle_ops.numbering.booking',
        'document_prefix_service_request' => 'usc_vehicle_ops.numbering.service_request',
        'document_prefix_reimbursement_batch' => 'usc_vehicle_ops.numbering.reimbursement_batch',
        'service_threshold_km' => 'usc_vehicle_ops.thresholds.service_km',
        'daily_distance_threshold_km' => 'usc_vehicle_ops.thresholds.daily_distance_km',
        'claim_deadline_days' => 'usc_vehicle_ops.thresholds.claim_deadline_days',
        'document_reminder_days' => 'usc_vehicle_ops.thresholds.document_reminder_days',
        'rental_contract_reminder_days' => 'usc_vehicle_ops.thresholds.rental_contract_reminder_days',
        'vendor_escalation_days' => 'usc_vehicle_ops.thresholds.vendor_escalation_days',
        'toll_min_balance' => 'usc_vehicle_ops.thresholds.toll_min_balance',
        'booking_lead_days' => 'usc_vehicle_ops.thresholds.booking_lead_days',
        'consumption_deviation_percent' => 'usc_vehicle_ops.thresholds.consumption_deviation_percent',
        'refuel_min_gap_hours' => 'usc_vehicle_ops.thresholds.refuel_min_gap_hours',
        'usage_average_days' => 'usc_vehicle_ops.usage_average_days',
    ];

    /** @return array<string, mixed> seluruh pengaturan tersimpan. */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::all()
                ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->typed_value])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $stored = $this->all();

        if (array_key_exists($key, $stored) && $stored[$key] !== null) {
            return $stored[$key];
        }

        if (isset(self::CONFIG_FALLBACK[$key])) {
            return config(self::CONFIG_FALLBACK[$key], $default);
        }

        return $default;
    }

    public function integer(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float) $this->get($key, $default);
    }

    public function string(string $key, string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    public function set(string $key, mixed $value, string $type = 'string', string $group = 'umum'): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        $setting->fill([
            'type' => $setting->exists ? $setting->type : $type,
            'group' => $setting->exists ? $setting->group : $group,
        ]);

        $setting->value = Setting::castForStorage($value, $setting->type);
        $setting->save();

        $this->flush();
    }

    /** @param array<string, mixed> $values */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = Setting::castForStorage($value, $setting->type ?? 'string');
            $setting->save();
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
