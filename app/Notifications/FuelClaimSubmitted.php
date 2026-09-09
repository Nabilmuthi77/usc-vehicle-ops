<?php

namespace App\Notifications;

use App\Models\FuelTransaction;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M3-06 — klaim BBM baru menunggu verifikasi Admin GA. */
class FuelClaimSubmitted extends UscVehicleOpsNotification
{
    public function __construct(public readonly FuelTransaction $transaction) {}

    public static function eventKey(): string
    {
        return 'fuel_claim_submitted';
    }

    public function title(): string
    {
        return 'Klaim BBM diajukan';
    }

    public function detail(): string
    {
        return sprintf(
            '%s mengajukan klaim BBM %s sebesar Rp %s.',
            $this->transaction->claimant?->name,
            $this->transaction->vehicle?->plate_number,
            number_format((float) $this->transaction->total_cost, 0, ',', '.'),
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
        return route('fuel.show', $this->transaction);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Klaim BBM menunggu verifikasi - USC Vehicle Ops')
            ->view('emails.fuel.submitted', [
                'user' => $notifiable,
                'transaction' => $this->transaction,
                'url' => $this->actionUrl(),
            ]);
    }
}
