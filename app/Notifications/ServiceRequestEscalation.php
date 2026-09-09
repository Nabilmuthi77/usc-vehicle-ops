<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M4-20 — eskalasi permintaan servis yang belum direspons vendor. */
class ServiceRequestEscalation extends UscVehicleOpsNotification
{
    public function __construct(
        public readonly ServiceRequest $request,
        public readonly int $daysWaiting,
    ) {}

    public static function eventKey(): string
    {
        return 'service_request_escalation';
    }

    public function title(): string
    {
        return 'Permintaan servis belum direspons vendor';
    }

    public function detail(): string
    {
        return sprintf(
            '%s (%s) belum direspons %s selama %d hari.',
            $this->request->request_number,
            $this->request->vehicle?->plate_number,
            $this->request->vendor?->name,
            $this->daysWaiting,
        );
    }

    public function icon(): string
    {
        return 'exclamation';
    }

    public function category(): string
    {
        return 'Servis';
    }

    public function actionUrl(): string
    {
        return route('service-requests.show', $this->request);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Eskalasi permintaan servis '.$this->request->request_number.' - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->detail())
            ->line('Kendaraan: '.$this->request->vehicle?->plate_number)
            ->line('Vendor: '.$this->request->vendor?->name)
            ->line('Kontak PIC: '.($this->request->vendor?->pic_phone ?: '-'))
            ->line('Dikirim pada: '.$this->request->sent_at?->format('d-m-Y'))
            ->line('Mohon ditindaklanjuti dengan menghubungi vendor.')
            ->action('Lihat Permintaan Servis', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
