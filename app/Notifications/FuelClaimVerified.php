<?php

namespace App\Notifications;

use App\Models\FuelTransaction;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M3-09 — klaim BBM disetujui Admin GA. */
class FuelClaimVerified extends UscVehicleOpsNotification
{
    public function __construct(public readonly FuelTransaction $transaction) {}

    public static function eventKey(): string
    {
        return 'fuel_claim_verified';
    }

    public function title(): string
    {
        return 'Klaim BBM terverifikasi';
    }

    public function detail(): string
    {
        return sprintf(
            'Klaim %s nota %s disetujui sebesar Rp %s.',
            $this->transaction->vehicle?->plate_number,
            $this->transaction->receipt_number,
            number_format($this->transaction->claimable_amount, 0, ',', '.'),
        );
    }

    public function icon(): string
    {
        return 'check-circle';
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
            ->subject('Klaim BBM Anda terverifikasi - USC Vehicle Ops')
            ->view('emails.fuel.verified', [
                'user' => $notifiable,
                'transaction' => $this->transaction,
                'url' => $this->actionUrl(),
            ]);
    }
}
