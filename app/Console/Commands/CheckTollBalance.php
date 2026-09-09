<?php

namespace App\Console\Commands;

use App\Notifications\TollBalanceLow;
use App\Services\NotificationDispatcher;
use App\Services\TollBalanceService;
use Illuminate\Console\Command;

/**
 * PRD §10.3 — `usc_vehicle_ops:check-toll-balance`, harian 07:00.
 *
 * FR-M5-07 — peringatan saldo kartu e-toll di bawah ambang minimum.
 */
class CheckTollBalance extends Command
{
    protected $signature = 'usc_vehicle_ops:check-toll-balance';

    protected $description = 'Kirim peringatan kartu e-toll dengan saldo dibawah minimum saldo';

    public function handle(TollBalanceService $tollBalance, NotificationDispatcher $notifier): int
    {
        $cards = $tollBalance->cardsBelowMinimum();

        foreach ($cards as $card) {
            $notifier->sendToApprovers(new TollBalanceLow($card));
        }

        $this->info(sprintf('%d kartu e-toll berada di bawah saldo minimum.', $cards->count()));

        return self::SUCCESS;
    }
}
