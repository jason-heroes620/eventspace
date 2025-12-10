<?php

namespace App\Mail;

use App\Models\EventApplicationGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\EventGroups;
use Illuminate\Support\Facades\Log;

class ApplicationApprovedResponse extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected EventGroups $event,
        protected EventApplicationGroup $application,
        protected $payment_link,
        protected $total,
        protected $reference_link,
        protected $deposit,
        protected $items,
        protected $deposit_amount,
        protected $due_date,
        protected $subTotal,
        protected $downpayment_amount,
        protected $balance
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->event->event_group . ' - Application Is Confirmed',
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
                'event_name' => $this->event->event_group,
                'due_date' => $this->due_date,
                'payment_link' => $this->payment_link,
                'payment' => number_format($this->total, 2),
                'upload_reference_link' => $this->reference_link,
                'deposit' => $this->deposit,
                'deposit_amount' => number_format($this->deposit_amount, 2),
                'discount' => $this->application->discount ?? null,
                'discount_value' => $this->application->discount ? $this->application->discount_value : null,
                'items' => $this->items,
                'subTotal' => $this->subTotal,
                'downpayment_amount' => number_format($this->downpayment_amount, 2),
                'balance' => number_format($this->balance, 2),
                'balanceAndDeposit' => number_format($this->balance + $this->deposit_amount, 2),
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
