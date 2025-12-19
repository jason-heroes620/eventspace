<?php

namespace App\Mail;

use App\Models\EventApplicationGroup;
use App\Models\EventDepositRefund;
use App\Models\EventPaymentReference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefundNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected EventApplicationGroup $application,
        protected EventPaymentReference $payment,
        protected EventDepositRefund $deposit,
        protected string $deposit_file
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Heroes: Deposit Has Been Refunded',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.refund-notification',
            with: [
                'contact_person' => $this->application->contact_person,
                'deposit' => $this->deposit->refund_amount,
                'bank' => $this->payment->bank,
                'account_no' => $this->payment->account_no,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $filename = $this->deposit_file;
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        Log::info($filename);
        Log::info($extension);
        if ($extension == 'pdf')
            $mime = 'application/pdf';
        else
            $mime = 'application/octet-stream';

        return [
            Attachment::fromPath(
                asset('storage/' . $this->deposit_file)
            )
                ->as('deposit_refund.' . $extension)
                ->withMime($mime)
        ];
    }
}
