<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteInquiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Quote from ' . $this->quote->tenant->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.quote-inquiry',
        );
    }

    public function attachments(): array
    {
        // Placeholder for PDF attachment
        return [];
    }
}
