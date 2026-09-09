<?php

namespace App\Notifications;

use App\Models\RentalContract;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M4-22 — pengingat kontrak sewa akan berakhir (default H-60). */
class RentalContractExpiring extends UscVehicleOpsNotification
{
    public function __construct(public readonly RentalContract $contract) {}

    public static function eventKey(): string
    {
        return 'rental_contract_expiring';
    }

    public function title(): string
    {
        return 'Kontrak sewa akan berakhir';
    }

    public function detail(): string
    {
        return sprintf(
            'Kontrak %s dengan %s berakhir dalam %d hari (%s).',
            $this->contract->contract_number,
            $this->contract->vendor?->name,
            $this->contract->days_remaining,
            $this->contract->end_date->format('d-m-Y'),
        );
    }

    public function icon(): string
    {
        return 'briefcase';
    }

    public function category(): string
    {
        return 'Kontrak Sewa';
    }

    public function actionUrl(): string
    {
        return route('rental-contracts.show', $this->contract);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kontrak sewa '.$this->contract->contract_number.' akan berakhir - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->detail())
            ->line('Kendaraan terkait: '.$this->contract->vehicles->pluck('plate_number')->implode(', '))
            ->line('Biaya sewa bulanan: Rp '
                .number_format((float) $this->contract->monthly_cost, 0, ',', '.'))
            ->line('PIC vendor: '.($this->contract->pic_name ?: '-')
                .' ('.($this->contract->pic_phone ?: '-').')')
            ->line('Mohon siapkan proses perpanjangan atau penggantian unit.')
            ->action('Lihat Kontrak', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
