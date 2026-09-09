<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingOverdue;
use App\Services\NotificationDispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * PRD §10.3 — `usc_vehicle_ops:check-overdue-booking`, tiap jam.
 *
 * FR-M2-24 — tandai peminjaman yang melewati tanggal pemakaian namun
 * belum dicatat pengembaliannya, lalu beri tahu pemohon dan Admin GA.
 */
class CheckOverdueBookings extends Command
{
    protected $signature = 'usc_vehicle_ops:check-overdue-booking';

    protected $description = 'Tandai peminjaman yang terlambat dikembalikan';

    public function handle(NotificationDispatcher $notifier): int
    {
        $bookings = Booking::query()
            ->with(['vehicle', 'requester', 'driver.user'])
            ->overdueCandidates()
            ->get();

        $notified = 0;

        foreach ($bookings as $booking) {
            $booking->forceFill(['is_overdue' => true])->save();

            // Pengingat dikirim maksimal sekali per hari agar tidak membanjiri.
            if ($booking->overdue_notified_at?->isToday()) {
                continue;
            }

            $notification = new BookingOverdue($booking);

            $notifier->send($booking->requester, $notification);
            $notifier->send($booking->driver?->user, $notification);
            $notifier->sendToApprovers($notification);

            $booking->forceFill(['overdue_notified_at' => Carbon::now()])->save();
            $notified++;
        }

        $this->info(sprintf(
            '%d peminjaman ditandai terlambat, %d notifikasi dikirim.',
            $bookings->count(),
            $notified,
        ));

        return self::SUCCESS;
    }
}
