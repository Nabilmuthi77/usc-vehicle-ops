<?php

namespace App\Console\Commands;

use App\Enums\ServiceScheduleStatus;
use App\Models\Booking;
use App\Models\FuelTransaction;
use App\Models\ServiceRequest;
use App\Models\TollCard;
use App\Models\VehicleServiceSchedule;
use App\Notifications\DailyDigest;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;

/**
 * FR-M7-03 — ringkasan harian via email untuk Admin GA berisi item
 * yang perlu ditindaklanjuti.
 */
class SendDailyDigest extends Command
{
    protected $signature = 'usc_vehicle_ops:daily-digest';

    protected $description = 'Kirim ringkasan harian ke Admin GA';

    public function handle(NotificationDispatcher $notifier): int
    {
        $summary = [
            'pending_bookings' => Booking::query()->pendingApproval()->count(),
            'pending_claims' => FuelTransaction::query()->awaitingVerification()->count(),
            'overdue_services' => VehicleServiceSchedule::query()
                ->active()
                ->ofStatus(ServiceScheduleStatus::JatuhTempo)
                ->count(),
            'upcoming_services' => VehicleServiceSchedule::query()
                ->active()
                ->ofStatus(ServiceScheduleStatus::Segera)
                ->count(),
            'overdue_bookings' => Booking::query()->where('is_overdue', true)
                ->where('status', \App\Enums\BookingStatus::SedangDigunakan)
                ->count(),
            'low_toll_cards' => TollCard::query()->belowMinimum()->count(),
            'open_service_requests' => ServiceRequest::query()->open()->count(),
        ];

        $notifier->sendToApprovers(new DailyDigest($summary));

        $this->info('Ringkasan harian dikirim ke Admin GA.');

        return self::SUCCESS;
    }
}
