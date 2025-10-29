<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Events;
use App\Models\EventApplications;
use Illuminate\Support\Facades\Log;

class ApplicationApprovedResponse extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected Events $event,
        protected EventApplications $application,
        protected $payment_link,
        protected $total,
        protected $reference_link,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->event->event_name . ' - Application Is Confirmed',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.application-approvedv2',
            with: [
                'contact_person' => $this->application->contact_person,
                'event_name' => $this->event->event_name,
                'event_date' => $this->event->event_date,
                'location' => $this->event->event_location,
                'venue' => $this->event->venue,
                'due_date' => $this->event->due_date,
                'payment_link' => $this->payment_link,
                'payment' => $this->total,
                'upload_reference_link' => $this->reference_link
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
