<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\VehicleDocument;
use App\Notifications\DocumentExpiring;
use App\Notifications\DriverLicenseExpiring;
use App\Services\NotificationDispatcher;
use App\Services\SettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * PRD §10.3 — `usc_vehicle_ops:check-documents`, harian 06:05.
 *
 * FR-M1-03 & FR-M1-04 — cek STNK/pajak/KIR/asuransi dan SIM driver
 * yang akan kedaluwarsa.
 */
class CheckDocuments extends Command
{
    protected $signature = 'usc_vehicle_ops:check-documents';

    protected $description = 'Cek dokumen kendaraan dan SIM driver yang akan kedaluwarsa';

    public function handle(NotificationDispatcher $notifier, SettingService $settings): int
    {
        $documents = VehicleDocument::query()
            ->with('vehicle')
            ->expiringSoon()
            ->get();

        foreach ($documents as $document) {
            $notifier->sendToApprovers(new DocumentExpiring($document));
        }

        // FR-M1-04 — SIM driver memakai ambang pengingat yang sama.
        $reminderDays = $settings->integer('document_reminder_days', 30);

        $drivers = Driver::query()
            ->active()
            ->whereDate('license_expiry', '>=', Carbon::today())
            ->whereDate('license_expiry', '<=', Carbon::today()->addDays($reminderDays))
            ->get();

        foreach ($drivers as $driver) {
            $notifier->sendToApprovers(new DriverLicenseExpiring($driver));
        }

        $this->info(sprintf(
            '%d dokumen kendaraan dan %d SIM driver akan kedaluwarsa.',
            $documents->count(),
            $drivers->count(),
        ));

        return self::SUCCESS;
    }
}
