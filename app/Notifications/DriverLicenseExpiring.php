<?php

namespace App\Notifications;

use App\Models\Driver;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M1-04 — pengingat SIM driver akan kedaluwarsa. */
class DriverLicenseExpiring extends UscVehicleOpsNotification
{
    public function __construct(public readonly Driver $driver) {}

    public static function eventKey(): string
    {
        return 'license_expiring';
    }

    public function title(): string
    {
        return 'SIM driver akan kedaluwarsa';
    }

    public function detail(): string
    {
        $days = (int) now()->startOfDay()->diffInDays($this->driver->license_expiry, false);

        return sprintf(
            '%s (%s) berakhir %s — %s.',
            $this->driver->license_type,
            $this->driver->name,
            $this->driver->license_expiry->format('d-m-Y'),
            $days < 0 ? 'sudah lewat' : 'sisa '.$days.' hari',
        );
    }

    public function icon(): string
    {
        return 'exclamation';
    }

    public function category(): string
    {
        return 'Dokumen';
    }

    public function actionUrl(): string
    {
        return route('drivers.index');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SIM '.$this->driver->name.' akan kedaluwarsa - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->detail())
            ->line('Nomor SIM: '.$this->driver->license_number)
            ->line('Driver dengan SIM kedaluwarsa tidak dapat ditugaskan pada peminjaman baru.')
            ->action('Lihat Data Driver', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
