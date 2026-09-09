<?php

namespace App\Console\Commands;

use App\Enums\ServiceScheduleStatus;
use App\Models\Vehicle;
use App\Models\VehicleServiceSchedule;
use App\Notifications\ServiceScheduleAlert;
use App\Services\NotificationDispatcher;
use App\Services\ServiceScheduleService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * PRD §10.3 — `usc_vehicle_ops:check-service`, harian 06:00.
 *
 * FR-M4-07 — kirim notifikasi saat status berubah menjadi Segera, dan
 * pengingat berulang harian selama masih Jatuh Tempo.
 */
class CheckServiceSchedules extends Command
{
    protected $signature = 'usc_vehicle_ops:check-service {--vehicle= : Batasi pada satu ID kendaraan}';

    protected $description = 'Hitung ulang status servis berkala dan kirim notifikasi';

    public function handle(
        ServiceScheduleService $schedules,
        NotificationDispatcher $notifier,
    ): int {
        $vehicles = Vehicle::query()
            ->with('serviceSchedules.serviceType')
            ->when($this->option('vehicle'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $notified = 0;

        foreach ($vehicles as $vehicle) {
            $changed = $schedules->recalculateForVehicle($vehicle);

            foreach ($vehicle->serviceSchedules()->with('serviceType')->get() as $schedule) {
                if ($this->shouldNotify($schedule, $changed)) {
                    $notifier->sendToApprovers(new ServiceScheduleAlert($schedule));

                    $schedule->forceFill(['last_notified_at' => Carbon::now()])->save();
                    $notified++;
                }
            }
        }

        $this->info(sprintf(
            'Status servis %d kendaraan dihitung ulang, %d notifikasi dikirim.',
            $vehicles->count(),
            $notified,
        ));

        return self::SUCCESS;
    }

    /**
     * Notifikasi dikirim saat status baru berubah menjadi Segera,
     * atau berulang setiap hari selama berstatus Jatuh Tempo.
     *
     * @param  array<int, VehicleServiceSchedule>  $changed
     */
    private function shouldNotify(VehicleServiceSchedule $schedule, array $changed): bool
    {
        if (! $schedule->is_active || ! $schedule->status->needsAttention()) {
            return false;
        }

        $statusJustChanged = collect($changed)
            ->contains(fn (VehicleServiceSchedule $s) => $s->is($schedule));

        if ($statusJustChanged) {
            return true;
        }

        // Pengingat eskalasi harian hanya untuk yang sudah jatuh tempo.
        if ($schedule->status !== ServiceScheduleStatus::JatuhTempo) {
            return false;
        }

        return $schedule->last_notified_at === null
            || $schedule->last_notified_at->lt(Carbon::today());
    }
}
