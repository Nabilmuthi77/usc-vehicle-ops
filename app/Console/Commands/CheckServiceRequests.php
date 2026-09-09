<?php

namespace App\Console\Commands;

use App\Models\ServiceRequest;
use App\Notifications\ServiceRequestEscalation;
use App\Services\NotificationDispatcher;
use App\Services\SettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * PRD §10.3 — `usc_vehicle_ops:check-service-request`, harian 08:00.
 *
 * FR-M4-20 — eskalasi bila permintaan servis kendaraan sewa belum
 * ditanggapi vendor dalam N hari (default 3 hari kerja).
 */
class CheckServiceRequests extends Command
{
    protected $signature = 'usc_vehicle_ops:check-service-request';

    protected $description = 'Eskalasi permintaan servis vendor yang belum direspons';

    public function handle(NotificationDispatcher $notifier, SettingService $settings): int
    {
        $limit = $settings->integer('vendor_escalation_days', 3);

        $requests = ServiceRequest::query()
            ->with(['vehicle', 'vendor'])
            ->awaitingVendor()
            ->get();

        $escalated = 0;

        foreach ($requests as $request) {
            $waiting = $this->workingDaysSince($request->sent_at);

            if ($waiting < $limit) {
                continue;
            }

            // Eskalasi diulang maksimal sekali sehari.
            if ($request->escalated_at?->isToday()) {
                continue;
            }

            $notifier->sendToApprovers(new ServiceRequestEscalation($request, $waiting));

            $request->forceFill(['escalated_at' => Carbon::now()])->save();
            $escalated++;
        }

        $this->info(sprintf(
            '%d permintaan servis menunggu vendor, %d dieskalasi.',
            $requests->count(),
            $escalated,
        ));

        return self::SUCCESS;
    }

    /** Hitung hari kerja (Senin–Jumat) sejak permintaan dikirim. */
    private function workingDaysSince(Carbon $sentAt): int
    {
        $days = 0;
        $cursor = $sentAt->copy()->startOfDay();
        $today = Carbon::today();

        while ($cursor->lt($today)) {
            $cursor->addDay();

            if (! $cursor->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }
}
