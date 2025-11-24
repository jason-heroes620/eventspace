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
        protected $deposit,
        protected $booth_type,
        protected $subTotal,
        protected $deposit_amount,
        protected $due_date,
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
        Log::info('mail data');
        Log::info($this->application);
        Log::info($this->event);
        return new Content(
            view: 'mails.application-approvedv2',
            with: [
                'contact_person' => $this->application->contact_person,
                'event_name' => $this->event->event_name,
                'event_date' => $this->event->event_date,
                'location' => $this->event->event_location,
                'venue' => $this->event->venue,
                'due_date' => $this->due_date,
                'payment_link' => $this->payment_link,
                'payment' => number_format($this->total, 2),
                'upload_reference_link' => $this->reference_link,
                'booth_type' => $this->booth_type,
                'booth_qty' => $this->application->booth_qty,
                'deposit' => $this->deposit,
                'subTotal' => number_format($this->subTotal, 2),
                'deposit_amount' => number_format($this->deposit_amount, 2),
                'discount' => $this->application->discount ?? null,
                'discount_value' => $this->application->discount ? $this->application->discount_value : null,
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
