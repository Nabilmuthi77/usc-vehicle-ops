<?php

namespace App\Services;

use App\Models\FuelTransaction;
use App\Models\Vehicle;

/**
 * FR-M3-04 & BR-05 — perhitungan konsumsi BBM metode full-to-full.
 *
 * `konsumsi (km/L) = (odometer penuh ke-n − odometer penuh ke-(n-1))
 *                     ÷ Σ liter di antara kedua pengisian penuh`
 *
 * Pengisian parsial tidak menghasilkan angka konsumsi tersendiri; liternya
 * ikut diperhitungkan pada pengisian penuh berikutnya.
 */
class FuelConsumptionService
{
    /**
     * Hitung ulang konsumsi sebuah transaksi dan simpan hasilnya.
     *
     * @return array{km: int|null, consumption: float|null}
     */
    public function recalculate(FuelTransaction $transaction): array
    {
        $result = ['km' => null, 'consumption' => null];

        // Hanya pengisian penuh yang menutup satu siklus perhitungan.
        if (! $transaction->is_full_tank) {
            $transaction->forceFill([
                'km_since_last_fill' => null,
                'consumption_km_per_liter' => null,
            ])->save();

            return $result;
        }

        $previousFull = $this->previousFullTank($transaction);

        if ($previousFull === null) {
            $transaction->forceFill([
                'km_since_last_fill' => null,
                'consumption_km_per_liter' => null,
            ])->save();

            return $result;
        }

        $distance = (int) $transaction->odometer - (int) $previousFull->odometer;

        // Σ liter setelah pengisian penuh sebelumnya sampai dengan transaksi ini.
        $liters = (float) FuelTransaction::query()
            ->where('vehicle_id', $transaction->vehicle_id)
            ->where('transaction_datetime', '>', $previousFull->transaction_datetime)
            ->where('transaction_datetime', '<=', $transaction->transaction_datetime)
            ->sum('liters');

        $consumption = ($distance > 0 && $liters > 0)
            ? round($distance / $liters, 2)
            : null;

        $transaction->forceFill([
            'km_since_last_fill' => $distance > 0 ? $distance : null,
            'consumption_km_per_liter' => $consumption,
        ])->save();

        return ['km' => $distance > 0 ? $distance : null, 'consumption' => $consumption];
    }

    /** Pengisian penuh terakhir sebelum transaksi ini pada kendaraan yang sama. */
    public function previousFullTank(FuelTransaction $transaction): ?FuelTransaction
    {
        return FuelTransaction::query()
            ->where('vehicle_id', $transaction->vehicle_id)
            ->where('is_full_tank', true)
            ->where('transaction_datetime', '<', $transaction->transaction_datetime)
            ->when($transaction->exists, fn ($q) => $q->whereKeyNot($transaction->getKey()))
            ->orderByDesc('transaction_datetime')
            ->first();
    }

    /**
     * Rata-rata konsumsi historis kendaraan, dipakai sebagai pembanding
     * pada deteksi anomali (FR-M3-05b).
     */
    public function historicalAverage(Vehicle $vehicle, ?FuelTransaction $except = null): ?float
    {
        $average = FuelTransaction::query()
            ->where('vehicle_id', $vehicle->getKey())
            ->whereNotNull('consumption_km_per_liter')
            ->when($except?->exists, fn ($q) => $q->whereKeyNot($except->getKey()))
            ->avg('consumption_km_per_liter');

        return $average === null ? null : round((float) $average, 2);
    }

    /**
     * Hitung ulang seluruh transaksi sebuah kendaraan secara berurutan.
     * Dipakai setelah koreksi data historis (Job RecalculateConsumption).
     */
    public function recalculateVehicle(Vehicle $vehicle): int
    {
        $transactions = FuelTransaction::query()
            ->where('vehicle_id', $vehicle->getKey())
            ->orderBy('transaction_datetime')
            ->get();

        foreach ($transactions as $transaction) {
            $this->recalculate($transaction);
        }

        return $transactions->count();
    }

    /**
     * FR-M3-16 — tren konsumsi bulanan sebuah kendaraan.
     *
     * @return array<int, array{period: string, average: float}>
     */
    public function monthlyTrend(Vehicle $vehicle, int $months = 12): array
    {
        return FuelTransaction::query()
            ->where('vehicle_id', $vehicle->getKey())
            ->whereNotNull('consumption_km_per_liter')
            ->where('transaction_datetime', '>=', now()->subMonths($months)->startOfMonth())
            ->orderBy('transaction_datetime')
            ->get(['transaction_datetime', 'consumption_km_per_liter'])
            ->groupBy(fn (FuelTransaction $t) => $t->transaction_datetime->format('Y-m'))
            ->map(fn ($group, $period) => [
                'period' => $period,
                'average' => round((float) $group->avg('consumption_km_per_liter'), 2),
            ])
            ->values()
            ->all();
    }
}
