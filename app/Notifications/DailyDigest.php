<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** FR-M7-03 — ringkasan harian item yang perlu ditindaklanjuti Admin GA. */
class DailyDigest extends UscVehicleOpsNotification
{
    /** @param array<string, int|string> $summary */
    public function __construct(public readonly array $summary) {}

    public static function eventKey(): string
    {
        return 'daily_digest';
    }

    public function title(): string
    {
        return 'Ringkasan harian USC_VEHICLE_OPS';
    }

    public function detail(): string
    {
        return sprintf(
            '%d pengajuan menunggu approval, %d klaim BBM menunggu verifikasi, '
            .'%d servis jatuh tempo, %d peminjaman terlambat.',
            $this->summary['pending_bookings'] ?? 0,
            $this->summary['pending_claims'] ?? 0,
            $this->summary['overdue_services'] ?? 0,
            $this->summary['overdue_bookings'] ?? 0,
        );
    }

    public function icon(): string
    {
        return 'bell';
    }

    public function category(): string
    {
        return 'Ringkasan';
    }

    public function actionUrl(): string
    {
        return route('dashboard');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ringkasan harian '.now()->format('d-m-Y').' - USC Vehicle Ops')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Berikut item yang perlu ditindaklanjuti hari ini:')
            ->line('• Pengajuan peminjaman menunggu approval: '.($this->summary['pending_bookings'] ?? 0))
            ->line('• Klaim BBM menunggu verifikasi: '.($this->summary['pending_claims'] ?? 0))
            ->line('• Servis jatuh tempo: '.($this->summary['overdue_services'] ?? 0))
            ->line('• Servis segera: '.($this->summary['upcoming_services'] ?? 0))
            ->line('• Peminjaman terlambat dikembalikan: '.($this->summary['overdue_bookings'] ?? 0))
            ->line('• Kartu e-toll saldo dibawah minimum saldo: '.($this->summary['low_toll_cards'] ?? 0))
            ->line('• Permintaan servis vendor terbuka: '.($this->summary['open_service_requests'] ?? 0))
            ->action('Buka Dashboard', $this->actionUrl())
            ->salutation('Terima kasih, USC_VEHICLE_OPS');
    }
}
