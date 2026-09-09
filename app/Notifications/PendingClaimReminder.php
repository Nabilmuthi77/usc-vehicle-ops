<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Perintah `usc_vehicle_ops:remind-pending-claim` — pengingat mingguan klaim BBM
 * yang masih menunggu verifikasi Admin GA.
 */
class PendingClaimReminder extends UscVehicleOpsNotification
{
    public function __construct(
        public readonly int $pendingCount,
        public readonly float $pendingAmount,
        public readonly ?string $oldestClaimDate = null,
    ) {}

    public static function eventKey(): string
    {
        return 'pending_claim_reminder';
    }

    public function title(): string
    {
        return 'Klaim BBM menunggu verifikasi';
    }

    public function detail(): string
    {
        return sprintf(
            'Terdapat %d klaim BBM senilai Rp %s yang menunggu verifikasi.',
            $this->pendingCount,
            number_format($this->pendingAmount, 0, ',', '.'),
        );
    }

    public function icon(): string
    {
        return 'droplet';
    }

    public function category(): string
    {
        return 'BBM';
    }

    public function actionUrl(): string
    {
        return route('fuel.index', ['status' => 'diajukan']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->pendingCount.' klaim BBM menunggu verifikasi - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->detail());

        if ($this->oldestClaimDate !== null) {
            $message->line('Klaim terlama diajukan pada '.$this->oldestClaimDate.'.');
        }

        return $message
            ->action('Verifikasi Sekarang', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
