<?php

namespace App\Services;

use App\Enums\ServiceCategory;
use App\Enums\ServiceScheduleStatus;
use App\Models\ServiceRecord;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehicleServiceSchedule;
use Illuminate\Support\Carbon;

/**
 * FR-M4-04 s.d. FR-M4-09 & BR-06/BR-07 — perhitungan status servis berkala.
 *
 * Berlaku identik untuk seluruh kepemilikan kendaraan (BR-17); yang berbeda
 * hanya tindak lanjutnya (bengkel sendiri vs permintaan servis ke vendor).
 */
class ServiceScheduleService
{
    public function __construct(private readonly SettingService $settings) {}

    /** Ambang KM untuk status "Segera Servis" (FR-M4-04, default 1.000 km). */
    public function threshold(): int
    {
        return $this->settings->integer('service_threshold_km', 1000);
    }

    /**
     * Hitung ulang seluruh jadwal aktif sebuah kendaraan.
     *
     * @return array<int, VehicleServiceSchedule> jadwal yang statusnya berubah
     */
    public function recalculateForVehicle(Vehicle $vehicle): array
    {
        $changed = [];

        $schedules = $vehicle->serviceSchedules()->where('is_active', true)->get();

        foreach ($schedules as $schedule) {
            if ($this->recalculate($schedule, $vehicle)) {
                $changed[] = $schedule;
            }
        }

        return $changed;
    }

    /**
     * Hitung ulang satu jadwal.
     *
     * BR-06 — `sisa_km = (odometer_servis_terakhir + interval_km) − odometer_saat_ini`.
     * BR-07 — jatuh tempo diambil dari interval KM atau waktu, mana yang lebih dahulu.
     *
     * @return bool true bila status berubah (dipakai untuk memicu notifikasi)
     */
    public function recalculate(VehicleServiceSchedule $schedule, ?Vehicle $vehicle = null): bool
    {
        $vehicle ??= $schedule->vehicle;
        $previousStatus = $schedule->status;

        // BR-06 — bila belum pernah servis, pakai odometer awal pencatatan.
        $baseOdometer = $schedule->last_service_odometer ?? (int) $vehicle->initial_odometer;
        $currentOdometer = (int) $vehicle->current_odometer;

        $nextDueOdometer = $schedule->interval_km
            ? $baseOdometer + (int) $schedule->interval_km
            : null;

        $baseDate = $schedule->last_service_date ?? $vehicle->created_at?->toDate();
        $nextDueDate = ($schedule->interval_months && $baseDate)
            ? Carbon::parse($baseDate)->addMonths((int) $schedule->interval_months)->toDateString()
            : null;

        $remainingKm = $nextDueOdometer !== null
            ? $nextDueOdometer - $currentOdometer
            : null;

        $schedule->fill([
            'next_due_odometer' => $nextDueOdometer,
            'next_due_date' => $nextDueDate,
            'remaining_km' => $remainingKm,
            'estimated_due_date' => $this->estimateDueDate($vehicle, $remainingKm),
            'status' => $this->resolveStatus($remainingKm, $nextDueDate),
        ]);

        if ($schedule->status !== $previousStatus) {
            $schedule->status_changed_at = Carbon::now();
        }

        $schedule->save();

        return $schedule->status !== $previousStatus;
    }

    /**
     * FR-M4-04 & BR-07 — status akhir diambil dari kondisi paling mendesak
     * antara sisa kilometer dan jatuh tempo waktu.
     */
    private function resolveStatus(?int $remainingKm, ?string $nextDueDate): ServiceScheduleStatus
    {
        $threshold = $this->threshold();
        $byKm = ServiceScheduleStatus::Aman;

        if ($remainingKm !== null) {
            $byKm = match (true) {
                $remainingKm <= 0 => ServiceScheduleStatus::JatuhTempo,
                $remainingKm <= $threshold => ServiceScheduleStatus::Segera,
                default => ServiceScheduleStatus::Aman,
            };
        }

        $byDate = ServiceScheduleStatus::Aman;

        if ($nextDueDate !== null) {
            $due = Carbon::parse($nextDueDate);
            $daysLeft = Carbon::today()->diffInDays($due, false);

            $byDate = match (true) {
                $daysLeft <= 0 => ServiceScheduleStatus::JatuhTempo,
                // Ambang waktu memakai proporsi setara: 30 hari terakhir.
                $daysLeft <= 30 => ServiceScheduleStatus::Segera,
                default => ServiceScheduleStatus::Aman,
            };
        }

        return $byKm->urgency() <= $byDate->urgency() ? $byKm : $byDate;
    }

    /**
     * FR-M4-05 — perkiraan tanggal jatuh tempo dari rata-rata pemakaian
     * km/hari 30 hari terakhir.
     */
    private function estimateDueDate(Vehicle $vehicle, ?int $remainingKm): ?string
    {
        if ($remainingKm === null || $remainingKm <= 0) {
            return null;
        }

        $days = $this->settings->integer('usage_average_days', 30);
        $average = app(OdometerService::class)->averageDailyUsage($vehicle, $days);

        if ($average <= 0) {
            return null;
        }

        return Carbon::today()->addDays((int) ceil($remainingKm / $average))->toDateString();
    }

    /**
     * FR-M4-09 — setelah realisasi servis, jadwal berikutnya di-generate
     * otomatis dari odometer & tanggal servis tersebut.
     */
    public function applyServiceRecord(ServiceRecord $record): ?VehicleServiceSchedule
    {
        // Servis insidental tidak menggeser jadwal berkala.
        if ($record->category !== ServiceCategory::Berkala || $record->service_type_id === null) {
            return null;
        }

        $schedule = VehicleServiceSchedule::firstOrNew([
            'vehicle_id' => $record->vehicle_id,
            'service_type_id' => $record->service_type_id,
        ]);

        if (! $schedule->exists) {
            $type = ServiceType::find($record->service_type_id);
            $schedule->interval_km = $type?->default_interval_km;
            $schedule->interval_months = $type?->default_interval_months;
            $schedule->is_active = true;
        }

        $schedule->fill([
            'last_service_odometer' => $record->odometer,
            'last_service_date' => $record->service_date,
        ])->save();

        $this->recalculate($schedule, $record->vehicle);

        return $schedule;
    }

    /**
     * FR-M4-01 — siapkan jadwal untuk seluruh jenis servis aktif pada
     * kendaraan yang baru didaftarkan.
     */
    public function bootstrapForVehicle(Vehicle $vehicle): void
    {
        foreach (ServiceType::active()->get() as $type) {
            $schedule = VehicleServiceSchedule::firstOrNew([
                'vehicle_id' => $vehicle->getKey(),
                'service_type_id' => $type->getKey(),
            ]);

            if ($schedule->exists) {
                continue;
            }

            $schedule->fill([
                'interval_km' => $type->default_interval_km,
                'interval_months' => $type->default_interval_months,
                'last_service_odometer' => $vehicle->initial_odometer,
                'is_active' => true,
            ])->save();

            $this->recalculate($schedule, $vehicle);
        }
    }
}
