<?php

namespace App\Mail;

use App\Models\EventApplicationGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EventApplications;
use App\Models\EventGroups;
use App\Models\EventPayments;
use App\Models\Events;

class PaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected EventGroups $event,
        protected EventApplicationGroup $application,
        protected EventPayments $payment
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->event->event_group . ' Payment Received',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.admin-payment',
            with: [
                'contact_person' => $this->application->contact_person,
                'event_name' => $this->event->event_name,
                'contact_no' => $this->application->contact_no,
                'email' => $this->application->email,
                'organization' => $this->application->organization,
                'payment_id' => $this->payment->id,
                'payment_date' => date('d/m/Y H:i', strtotime($this->payment->created))
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
        return [];
    }
}
