<?php

namespace App\Services;

use App\Enums\OdometerSource;
use App\Exceptions\BusinessRuleException;
use App\Models\OdometerLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * FR-M4-03 & BR-03 — satu pintu untuk seluruh pembaruan odometer.
 *
 * Setiap sumber (serah terima, pengembalian, pengisian BBM, realisasi servis,
 * input manual) wajib melewati service ini agar log odometer lengkap dan
 * status jadwal servis ikut dihitung ulang.
 */
class OdometerService
{
    public function __construct(
        private readonly ServiceScheduleService $serviceSchedules,
    ) {}

    /**
     * Catat pembacaan odometer dan perbarui odometer kendaraan.
     *
     * @param  Model|null  $sourceable  dokumen sumber (Booking, FuelTransaction, ServiceRecord)
     * @param  bool  $isCorrection  BR-03 — koreksi mundur oleh Admin, wajib beralasan
     *
     * @throws BusinessRuleException bila pembacaan mundur tanpa alasan koreksi
     */
    public function record(
        Vehicle $vehicle,
        int $odometer,
        OdometerSource $source,
        ?Carbon $recordedAt = null,
        ?Model $sourceable = null,
        ?User $recordedBy = null,
        ?string $notes = null,
        bool $isCorrection = false,
        ?string $correctionReason = null,
    ): OdometerLog {
        $recordedAt ??= Carbon::now();

        if (! $isCorrection) {
            $this->assertNotBackward($vehicle, $odometer);
        } elseif (blank($correctionReason)) {
            throw BusinessRuleException::rule(
                'BR-03',
                'Koreksi odometer mundur wajib menyertakan alasan.',
            );
        }

        return DB::transaction(function () use (
            $vehicle, $odometer, $source, $recordedAt, $sourceable,
            $recordedBy, $notes, $isCorrection, $correctionReason
        ) {
            $log = new OdometerLog([
                'vehicle_id' => $vehicle->getKey(),
                'odometer' => $odometer,
                'delta_km' => $odometer - (int) $vehicle->current_odometer,
                'recorded_at' => $recordedAt,
                'source' => $source,
                'recorded_by' => $recordedBy?->getKey(),
                'is_correction' => $isCorrection,
                'correction_reason' => $correctionReason,
                'notes' => $notes,
            ]);

            if ($sourceable !== null) {
                $log->sourceable()->associate($sourceable);
            }

            $log->save();

            // Odometer kendaraan hanya maju, kecuali memang sedang dikoreksi.
            if ($isCorrection || $odometer > (int) $vehicle->current_odometer) {
                $vehicle->forceFill(['current_odometer' => $odometer])->save();
            }

            // FR-M4-04 — status jadwal servis mengikuti odometer terbaru.
            $this->serviceSchedules->recalculateForVehicle($vehicle->refresh());

            return $log;
        });
    }

    /**
     * BR-03 — odometer bersifat monotonik naik.
     *
     * @throws BusinessRuleException
     */
    public function assertNotBackward(Vehicle $vehicle, int $odometer): void
    {
        if ($odometer < (int) $vehicle->current_odometer) {
            throw BusinessRuleException::rule('BR-03', sprintf(
                'Odometer %s km lebih kecil dari pencatatan terakhir (%s km). '
                .'Gunakan menu koreksi Admin bila memang perlu diperbaiki.',
                number_format($odometer, 0, ',', '.'),
                number_format((int) $vehicle->current_odometer, 0, ',', '.'),
            ));
        }
    }

    /**
     * FR-M4-05 — rata-rata pemakaian km/hari pada N hari terakhir.
     * Dipakai untuk memperkirakan tanggal jatuh tempo servis.
     */
    public function averageDailyUsage(Vehicle $vehicle, int $days = 30): float
    {
        $since = Carbon::today()->subDays($days);

        $logs = $vehicle->odometerLogs()
            ->where('recorded_at', '>=', $since)
            ->where('is_correction', false)
            ->orderBy('recorded_at')
            ->get(['odometer', 'recorded_at']);

        if ($logs->count() < 2) {
            return 0.0;
        }

        $distance = (int) $logs->last()->odometer - (int) $logs->first()->odometer;
        $elapsed = max(1, (int) $logs->first()->recorded_at->diffInDays($logs->last()->recorded_at));

        return $distance <= 0 ? 0.0 : round($distance / $elapsed, 2);
    }
}
