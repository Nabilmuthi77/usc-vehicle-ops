<?php

namespace App\Notifications;

use App\Enums\ServiceScheduleStatus;
use App\Models\VehicleServiceSchedule;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * FR-M4-07 — notifikasi saat status berubah menjadi Segera, dan pengingat
 * berulang saat Jatuh Tempo.
 *
 * Berlaku identik untuk seluruh kepemilikan kendaraan (BR-17); isi pesan
 * menyesuaikan tindak lanjutnya (bengkel sendiri vs permintaan ke vendor).
 */
class ServiceScheduleAlert extends UscVehicleOpsNotification
{
    public function __construct(public readonly VehicleServiceSchedule $schedule) {}

    public static function eventKey(): string
    {
        return 'service_due';
    }

    public function title(): string
    {
        return $this->schedule->status === ServiceScheduleStatus::JatuhTempo
            ? 'Servis jatuh tempo'
            : 'Servis segera diperlukan';
    }

    public function detail(): string
    {
        $vehicle = $this->schedule->vehicle;
        $remaining = (int) $this->schedule->remaining_km;

        return $remaining <= 0
            ? sprintf(
                '%s sudah melewati jadwal %s sejauh %s km.',
                $vehicle?->plate_number,
                $this->schedule->serviceType?->name,
                number_format(abs($remaining), 0, ',', '.'),
            )
            : sprintf(
                '%s mendekati jadwal %s, sisa %s km.',
                $vehicle?->plate_number,
                $this->schedule->serviceType?->name,
                number_format($remaining, 0, ',', '.'),
            );
    }

    public function icon(): string
    {
        return 'wrench';
    }

    public function category(): string
    {
        return 'Servis';
    }

    public function actionUrl(): string
    {
        return route('service.index');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vehicle = $this->schedule->vehicle;

        $message = (new MailMessage)
            ->subject($this->title().' — '.$vehicle?->plate_number.' - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->detail())
            ->line('Kendaraan: '.$vehicle?->plate_number.' ('.$vehicle?->full_name.')')
            ->line('Jenis servis: '.$this->schedule->serviceType?->name)
            ->line('Odometer saat ini: '.number_format((int) $vehicle?->current_odometer, 0, ',', '.').' km')
            ->line('Jatuh tempo pada odometer: '
                .number_format((int) $this->schedule->next_due_odometer, 0, ',', '.').' km');

        if ($this->schedule->estimated_due_date !== null) {
            $message->line('Perkiraan tanggal jatuh tempo: '
                .$this->schedule->estimated_due_date->format('d-m-Y'));
        }

        // BR-17 — tindak lanjut berbeda menurut kepemilikan kendaraan.
        $message->line($vehicle?->isRented()
            ? 'Kendaraan ini berstatus sewa/leasing — buat permintaan servis ke vendor melalui sistem.'
            : 'Silakan jadwalkan servis ke bengkel rekanan.');

        return $message
            ->action('Buka Dashboard Servis', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
