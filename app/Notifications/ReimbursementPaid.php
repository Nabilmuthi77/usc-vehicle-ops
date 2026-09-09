<?php

namespace App\Notifications;

use App\Models\ReimbursementBatch;
use Illuminate\Notifications\Messages\MailMessage;

/** FR-M3-12 — batch reimbursement telah dibayar Finance. */
class ReimbursementPaid extends UscVehicleOpsNotification
{
    public function __construct(public readonly ReimbursementBatch $batch) {}

    public static function eventKey(): string
    {
        return 'reimbursement_paid';
    }

    public function title(): string
    {
        return 'Reimbursement telah dibayar';
    }

    public function detail(): string
    {
        return sprintf(
            'Batch %s sebesar Rp %s (%d klaim) telah dibayarkan.',
            $this->batch->batch_number,
            number_format((float) $this->batch->total_amount, 0, ',', '.'),
            (int) $this->batch->item_count,
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
        return route('reimbursements.show', $this->batch);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reimbursement '.$this->batch->batch_number.' telah dibayar - USC Vehicle Ops')
            ->view('emails.reimbursements.paid', [
                'user' => $notifiable,
                'batch' => $this->batch,
                'url' => $this->actionUrl(),
            ]);
    }
}
