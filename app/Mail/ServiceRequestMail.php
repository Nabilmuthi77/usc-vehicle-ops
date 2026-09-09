<?php

namespace App\Mail;

use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * FR-M4-17 — permintaan servis dikirim ke PIC vendor beserta lampiran PDF.
 */
class ServiceRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ServiceRequest $serviceRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Permintaan Servis '.$this->serviceRequest->request_number.' - USC Vehicle Ops',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-request',
            with: ['request' => $this->serviceRequest],
        );
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.service-request', [
            'request' => $this->serviceRequest->load(['vehicle', 'vendor', 'serviceType']),
            'company' => config('usc_vehicle_ops.company'),
        ]);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                str_replace('/', '-', $this->serviceRequest->request_number).'.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
