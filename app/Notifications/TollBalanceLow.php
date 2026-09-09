<?php

namespace App\Notifications;

use App\Models\TollCard;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M5-07 — peringatan saldo kartu e-toll di bawah ambang minimum. */
class TollBalanceLow extends UscVehicleOpsNotification
{
    public function __construct(public readonly TollCard $card) {}

    public static function eventKey(): string
    {
        return 'toll_balance_low';
    }

    public function title(): string
    {
        return 'Saldo kartu e-toll dibawah minimum saldo';
    }

    public function detail(): string
    {
        return sprintf(
            'Kartu %s (%s) tersisa Rp %s, di bawah ambang Rp %s.',
            $this->card->issuer,
            $this->card->vehicle?->plate_number ?? 'tanpa kendaraan',
            number_format((float) $this->card->balance, 0, ',', '.'),
            number_format((float) $this->card->min_balance_alert, 0, ',', '.'),
        );
    }

    public function icon(): string
    {
        return 'ticket';
    }

    public function category(): string
    {
        return 'Tol';
    }

    public function actionUrl(): string
    {
        return route('toll.index');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Saldo e-toll dibawah minimum saldo — '.$this->card->issuer.' - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->detail())
            ->line('Nomor kartu: '.$this->card->card_number)
            ->line('Mohon segera lakukan top-up agar operasional kendaraan tidak terganggu.')
            ->action('Lihat Kartu e-Toll', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
