<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * FR-M2-18 — hasil approval ke pemohon, memuat nomor polisi, merk/tipe,
 * serta nama & kontak driver bila ditugaskan.
 */
class BookingApproved extends UscVehicleOpsNotification
{
    public function __construct(public readonly Booking $booking) {}

    public static function eventKey(): string
    {
        return 'booking_approved';
    }

    public function title(): string
    {
        return 'Peminjaman disetujui';
    }

    public function detail(): string
    {
        return sprintf(
            '%s disetujui — unit %s (%s)%s.',
            $this->booking->booking_number,
            $this->booking->vehicle?->plate_number,
            $this->booking->vehicle?->full_name,
            $this->driverSummary(),
        );
    }

    public function icon(): string
    {
        return 'check-circle';
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
            ->subject('Peminjaman '.$this->booking->booking_number.' disetujui - USC Vehicle Ops')
            ->view('emails.bookings.approved', [
                'user' => $notifiable,
                'booking' => $this->booking,
                'url' => $this->actionUrl(),
            ]);
    }

    private function driverSummary(): string
    {
        return $this->booking->driver !== null
            ? ', driver '.$this->booking->driver->name
            : ', tanpa driver (self drive)';
    }
}
