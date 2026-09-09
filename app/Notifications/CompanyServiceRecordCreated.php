<?php

namespace App\Notifications;

use App\Models\ServiceRecord;
use Illuminate\Notifications\Messages\MailMessage;

class CompanyServiceRecordCreated extends UscVehicleOpsNotification
{
    public function __construct(public readonly ServiceRecord $record) {}

    public static function eventKey(): string
    {
        return 'company_service_record_created';
    }

    public function title(): string
    {
        $serviceName = $this->record->serviceType?->name ?? 'Insidental';
        return 'Servis Ditanggung Perusahaan (' . $serviceName . ')';
    }

    public function detail(): string
    {
        $serviceName = $this->record->serviceType?->name ?? 'Insidental';
        return sprintf(
            'Terdapat realisasi servis kendaraan %s %s sebesar Rp %s.',
            $this->record->vehicle?->plate_number,
            $serviceName,
            number_format((float) $this->record->total_cost, 0, ',', '.'),
        );
    }

    public function icon(): string
    {
        return 'tool';
    }

    public function category(): string
    {
        return 'Servis';
    }

    public function actionUrl(): string
    {
        return route('service.history', $this->record->vehicle_id);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->view('emails.service.company-borne', [
                'user' => $notifiable,
                'record' => $this->record,
                'title' => $this->title(),
                'detail' => $this->detail(),
                'url' => $this->actionUrl(),
            ]);
    }
}
