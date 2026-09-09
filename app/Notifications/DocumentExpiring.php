<?php

namespace App\Notifications;

use App\Models\VehicleDocument;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M1-03 & FR-M7-02 — dokumen kendaraan akan kedaluwarsa (H-30). */
class DocumentExpiring extends UscVehicleOpsNotification
{
    public function __construct(public readonly VehicleDocument $document) {}

    public static function eventKey(): string
    {
        return 'document_expiring';
    }

    public function title(): string
    {
        return 'Dokumen kendaraan akan kedaluwarsa';
    }

    public function detail(): string
    {
        return sprintf(
            '%s %s akan berakhir dalam %d hari (%s).',
            $this->document->document_type->label(),
            $this->document->vehicle?->plate_number,
            $this->document->days_remaining,
            $this->document->expiry_date->format('d-m-Y'),
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
        return route('vehicles.show', $this->document->vehicle_id);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->document->document_type->label().' '
                .$this->document->vehicle?->plate_number.' akan kedaluwarsa - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->detail())
            ->line('Kendaraan: '.$this->document->vehicle?->plate_number
                .' ('.$this->document->vehicle?->full_name.')')
            ->line('Nomor dokumen: '.($this->document->document_number ?: '-'))
            ->line('Mohon segera diproses perpanjangannya.')
            ->action('Lihat Kendaraan', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
