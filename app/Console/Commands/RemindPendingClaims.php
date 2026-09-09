<?php

namespace App\Console\Commands;

use App\Models\FuelTransaction;
use App\Notifications\PendingClaimReminder;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

/**
 * PRD §10.3 — `usc_vehicle_ops:remind-pending-claim`, mingguan Senin 08:00.
 *
 * Pengingat klaim BBM yang masih menunggu verifikasi Admin GA.
 */
class RemindPendingClaims extends Command
{
    protected $signature = 'usc_vehicle_ops:remind-pending-claim';

    protected $description = 'Ingatkan Admin GA atas klaim BBM yang menunggu verifikasi';

    public function handle(NotificationDispatcher $notifier): int
    {
        $claims = FuelTransaction::query()->awaitingVerification()->get();

        if ($claims->isEmpty()) {
            $this->info('Tidak ada klaim BBM yang menunggu verifikasi.');

            return self::SUCCESS;
        }

        $oldest = $claims->min('submitted_at');

        $notifier->sendToApprovers(new PendingClaimReminder(
            pendingCount: $claims->count(),
            pendingAmount: (float) $claims->sum('total_cost'),
            oldestClaimDate: $oldest?->format('d-m-Y'),
        ));

        $this->info(sprintf('Pengingat %d klaim BBM dikirim.', $claims->count()));

        return self::SUCCESS;
    }
}
