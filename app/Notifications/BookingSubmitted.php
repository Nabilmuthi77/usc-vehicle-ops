<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M7-02 — pengajuan baru masuk ke antrean approval Admin GA. */
class BookingSubmitted extends UscVehicleOpsNotification
{
    public function __construct(public readonly Booking $booking) {}

    public static function eventKey(): string
    {
        return 'booking_submitted';
    }

    public function title(): string
    {
        return 'Peminjaman baru menunggu approval';
    }

    public function detail(): string
    {
        return sprintf(
            '%s — %s mengajukan peminjaman untuk %s tujuan %s.',
            $this->booking->booking_number,
            $this->booking->requester?->name,
            $this->booking->booking_date->format('d-m-Y'),
            $this->booking->destination,
        );
    }

    public function icon(): string
    {
        return 'calendar';
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
            ->subject('Pengajuan peminjaman '.$this->booking->booking_number.' - USC Vehicle Ops')
            ->view('emails.bookings.submitted', [
                'user' => $notifiable,
                'booking' => $this->booking,
                'url' => $this->actionUrl(),
            ]);
    }
}
