<?php

namespace App\Console\Commands;

use App\Models\RentalContract;
use App\Notifications\RentalContractExpiring;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

/**
 * PRD §10.3 — `usc_vehicle_ops:check-rental-contract`, harian 06:10.
 *
 * FR-M4-22 — pengingat kontrak sewa akan berakhir (default H-60).
 */
class CheckRentalContracts extends Command
{
    protected $signature = 'usc_vehicle_ops:check-rental-contract';

    protected $description = 'Kirim pengingat kontrak sewa yang akan berakhir';

    public function handle(NotificationDispatcher $notifier): int
    {
        $contracts = RentalContract::query()
            ->with(['vendor', 'vehicles'])
            ->expiringSoon()
            ->get();

        foreach ($contracts as $contract) {
            $notifier->sendToApprovers(new RentalContractExpiring($contract));
        }

        $this->info(sprintf('%d kontrak sewa memasuki masa pengingat.', $contracts->count()));

        return self::SUCCESS;
    }
}
