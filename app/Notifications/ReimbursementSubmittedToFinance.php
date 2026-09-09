<?php

namespace App\Notifications;

use App\Models\ReimbursementBatch;
use Illuminate\Notifications\Messages\MailMessage;

class ReimbursementSubmittedToFinance extends UscVehicleOpsNotification
{
    public function __construct(public readonly ReimbursementBatch $batch) {}

    public static function eventKey(): string
    {
        return 'reimbursement_submitted_to_finance';
    }

    public function title(): string
    {
        return 'Reimbursement diserahkan ke Finance';
    }

    public function detail(): string
    {
        return sprintf(
            'Batch %s sebesar Rp %s (%d klaim) telah diserahkan untuk diproses pembayarannya.',
            $this->batch->batch_number,
            number_format((float) $this->batch->total_amount, 0, ',', '.'),
            (int) $this->batch->item_count,
        );
    }

    public function icon(): string
    {
        return 'file-text';
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
            ->subject('Batch Reimbursement '.$this->batch->batch_number.' diserahkan ke Finance - USC Vehicle Ops')
            ->view('emails.reimbursements.submitted_to_finance', [
                'user' => $notifiable,
                'batch' => $this->batch,
                'url' => $this->actionUrl(),
            ]);
    }
}
