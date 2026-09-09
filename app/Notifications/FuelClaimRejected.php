<?php

namespace App\Notifications;

use App\Models\FuelTransaction;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M3-09 — klaim BBM ditolak beserta alasannya. */
class FuelClaimRejected extends UscVehicleOpsNotification
{
    public function __construct(public readonly FuelTransaction $transaction) {}

    public static function eventKey(): string
    {
        return 'fuel_claim_rejected';
    }

    public function title(): string
    {
        return 'Klaim BBM ditolak';
    }

    public function detail(): string
    {
        return sprintf(
            'Klaim %s nota %s ditolak — %s',
            $this->transaction->vehicle?->plate_number,
            $this->transaction->receipt_number,
            $this->transaction->rejection_reason,
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
        return route('fuel.my-claims');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Klaim BBM Anda ditolak - USC Vehicle Ops')
            ->view('emails.fuel.rejected', [
                'user' => $notifiable,
                'transaction' => $this->transaction,
                'url' => $this->actionUrl(),
            ]);
    }
}
