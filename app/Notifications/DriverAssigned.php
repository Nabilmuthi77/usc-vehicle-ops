<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * FR-M2-18 — driver yang ditugaskan menerima tanggal, tujuan,
 * keperluan, dan durasi penugasan.
 */
class DriverAssigned extends UscVehicleOpsNotification
{
    public function __construct(public readonly Booking $booking) {}

    public static function eventKey(): string
    {
        return 'driver_assigned';
    }

    public function title(): string
    {
        return 'Penugasan mengemudi baru';
    }

    public function detail(): string
    {
        return sprintf(
            '%s — %s, tujuan %s (%s).',
            $this->booking->booking_number,
            $this->booking->booking_date->format('d-m-Y'),
            $this->booking->destination,
            $this->booking->duration_type->label(),
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
            ->subject('Penugasan mengemudi '.$this->booking->booking_number.' - USC Vehicle Ops')
            ->view('emails.bookings.driver-assigned', [
                'user' => $notifiable,
                'booking' => $this->booking,
                'url' => $this->actionUrl(),
            ]);
    }
}
