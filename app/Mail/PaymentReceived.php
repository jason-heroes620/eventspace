<?php

namespace App\Mail;

use App\Models\Booths;
use App\Models\EventApplications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Events;

class PaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Events $event,
        protected EventApplications $application,
        protected Booths $booth,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->event->event_name . ' Payment Received',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.merchant-payment',
            with: [
                'name' => $this->application->contact_person,
                'event_name' => $this->event->event_name,
                'event_date' => $this->event->event_date,
                'event_time' => $this->event->event_time,
                'event_location' => $this->event->event_location,
                'booth' => $this->booth->booth_type,
                'booth_qty' => $this->application->booth_qty
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
