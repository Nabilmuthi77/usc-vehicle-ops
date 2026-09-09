<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/** Pengajuan peminjaman dibatalkan oleh pemohon. */
class BookingCancelled extends UscVehicleOpsNotification
{
    public function __construct(public readonly Booking $booking) {}

    public static function eventKey(): string
    {
        return 'booking_cancelled';
    }

    public function title(): string
    {
        return 'Peminjaman dibatalkan';
    }

    public function detail(): string
    {
        return sprintf(
            '%s — %s membatalkan peminjaman untuk %s.',
            $this->booking->booking_number,
            $this->booking->requester?->name,
            $this->booking->booking_date->format('d-m-Y')
        );
    }

    public function icon(): string
    {
        return 'x-circle';
    }

    public function category(): string
    {
        return 'Peminjaman';
    }

    public function actionUrl(): string
    {
        return route('bookings.show', $this->booking);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pembatalan peminjaman '.$this->booking->booking_number.' - USC Vehicle Ops')
            ->view('emails.bookings.cancelled', [
                'user' => $notifiable,
                'booking' => $this->booking,
                'url' => $this->actionUrl(),
            ]);
    }
}
