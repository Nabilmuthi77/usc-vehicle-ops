<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M2-15 & FR-M7-02 — penolakan pengajuan beserta alasannya. */
class BookingRejected extends UscVehicleOpsNotification
{
    public function __construct(public readonly Booking $booking) {}

    public static function eventKey(): string
    {
        return 'booking_rejected';
    }

    public function title(): string
    {
        return 'Peminjaman ditolak';
    }

    public function detail(): string
    {
        return sprintf(
            '%s ditolak — %s',
            $this->booking->booking_number,
            $this->booking->rejection_reason,
        );
    }

    public function icon(): string
    {
        return 'exclamation';
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
            ->subject('Peminjaman '.$this->booking->booking_number.' ditolak - USC Vehicle Ops')
            ->view('emails.bookings.rejected', [
                'user' => $notifiable,
                'booking' => $this->booking,
                'url' => $this->actionUrl(),
            ]);
    }
}
