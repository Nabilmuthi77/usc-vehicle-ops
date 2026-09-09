<?php

namespace App\Services;

use App\Models\FuelTransaction;

/**
 * FR-M3-05 — penandaan otomatis transaksi BBM yang perlu direview.
 *
 * Empat pemeriksaan sesuai PRD:
 *   (a) liter melebihi kapasitas tangki
 *   (b) konsumsi menyimpang > 30% dari rata-rata historis kendaraan
 *   (c) odometer lebih kecil dari pencatatan sebelumnya
 *   (d) dua pengisian dalam < 2 jam
 */
class FuelAnomalyDetector
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly FuelConsumptionService $consumption,
    ) {}

    /**
     * Jalankan seluruh pemeriksaan dan simpan hasilnya pada transaksi.
     *
     * @return array<int, string> daftar alasan anomali (kosong bila wajar)
     */
    public function evaluate(FuelTransaction $transaction): array
    {
        $reasons = array_filter([
            $this->checkTankCapacity($transaction),
            $this->checkConsumptionDeviation($transaction),
            $this->checkOdometerRegression($transaction),
            $this->checkRefuelInterval($transaction),
        ]);

        $reasons = array_values($reasons);

        $transaction->forceFill([
            'is_anomaly' => $reasons !== [],
            'anomaly_reason' => $reasons === [] ? null : $reasons,
        ])->save();

        return $reasons;
    }

    /** (a) Liter melebihi kapasitas tangki kendaraan. */
    private function checkTankCapacity(FuelTransaction $transaction): ?string
    {
        $capacity = (float) ($transaction->vehicle->tank_capacity ?? 0);

        if ($capacity <= 0 || (float) $transaction->liters <= $capacity) {
            return null;
        }

        return sprintf(
            'Jumlah pengisian %.2f liter melebihi kapasitas tangki %.2f liter.',
            (float) $transaction->liters,
            $capacity,
        );
    }

    /** (b) Konsumsi menyimpang lebih dari ambang persentase dari rata-rata. */
    private function checkConsumptionDeviation(FuelTransaction $transaction): ?string
    {
        $current = $transaction->consumption_km_per_liter;

        if ($current === null) {
            return null;
        }

        $average = $this->consumption->historicalAverage($transaction->vehicle, $transaction);

        if ($average === null || $average <= 0) {
            return null;
        }

        $threshold = $this->settings->float('consumption_deviation_percent', 30);
        $deviation = abs(((float) $current - $average) / $average) * 100;

        if ($deviation <= $threshold) {
            return null;
        }

        return sprintf(
            'Konsumsi %.2f km/L menyimpang %.1f%% dari rata-rata historis %.2f km/L.',
            (float) $current,
            $deviation,
            $average,
        );
    }

    /** (c) Odometer lebih kecil dari pencatatan pengisian sebelumnya. */
    private function checkOdometerRegression(FuelTransaction $transaction): ?string
    {
        $previous = FuelTransaction::query()
            ->where('vehicle_id', $transaction->vehicle_id)
            ->where('transaction_datetime', '<', $transaction->transaction_datetime)
            ->when($transaction->exists, fn ($q) => $q->whereKeyNot($transaction->getKey()))
            ->orderByDesc('transaction_datetime')
            ->first(['odometer']);

        if ($previous === null || (int) $transaction->odometer >= (int) $previous->odometer) {
            return null;
        }

        return sprintf(
            'Odometer %s km lebih kecil dari pengisian sebelumnya (%s km).',
            number_format((int) $transaction->odometer, 0, ',', '.'),
            number_format((int) $previous->odometer, 0, ',', '.'),
        );
    }

    /** (d) Dua pengisian pada kendaraan yang sama dalam rentang < 2 jam. */
    private function checkRefuelInterval(FuelTransaction $transaction): ?string
    {
        $gapHours = $this->settings->integer('refuel_min_gap_hours', 2);

        $recent = FuelTransaction::query()
            ->where('vehicle_id', $transaction->vehicle_id)
            ->when($transaction->exists, fn ($q) => $q->whereKeyNot($transaction->getKey()))
            ->whereBetween('transaction_datetime', [
                $transaction->transaction_datetime->copy()->subHours($gapHours),
                $transaction->transaction_datetime->copy()->addHours($gapHours),
            ])
            ->orderByDesc('transaction_datetime')
            ->first(['transaction_datetime']);

        if ($recent === null) {
            return null;
        }

        return sprintf(
            'Terdapat pengisian lain pada %s, berjarak kurang dari %d jam.',
            $recent->transaction_datetime->format('d-m-Y H:i'),
            $gapHours,
        );
    }
}
