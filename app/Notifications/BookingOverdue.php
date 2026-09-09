<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M2-24 — peminjaman terlambat dikembalikan. */
class BookingOverdue extends UscVehicleOpsNotification
{
    public function __construct(public readonly Booking $booking) {}

    public static function eventKey(): string
    {
        return 'booking_overdue';
    }

    public function title(): string
    {
        return 'Peminjaman terlambat dikembalikan';
    }

    public function detail(): string
    {
        return sprintf(
            '%s (%s) belum dikembalikan, melewati tanggal pemakaian %s.',
            $this->booking->booking_number,
            $this->booking->vehicle?->plate_number,
            $this->booking->booking_date->format('d-m-Y'),
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
            ->subject('Peminjaman '.$this->booking->booking_number.' terlambat dikembalikan - USC Vehicle Ops')
            ->view('emails.bookings.overdue', [
                'user' => $notifiable,
                'booking' => $this->booking,
                'url' => $this->actionUrl(),
            ]);
    }
}
