<?php

namespace App\Mail;

use App\Models\CommercialDocument;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendCommercialDocumentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  CommercialDocument|Invoice $document
     * @param  string|null                $paymentUrl
     * @param  string|null                $customSubject
     */
    public function __construct(
        public CommercialDocument|Invoice $document,
        public ?string $paymentUrl = null,
        public ?string $customSubject = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $tenantName = $this->document->tenant->name ?? 'KMO-Flow';
        $number = $this->document->document_number ?? $this->document->invoice_number ?? 'Concept';
        $type = $this->getDocumentTitle();

        $subject = $this->customSubject ?: "{$type} {$number} van {$tenantName}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $tenant = $this->document->tenant;
        $customer = $this->document->customer;
        $number = $this->document->document_number ?? $this->document->invoice_number;
        $structuredRef = $this->document->structured_communication ?? $this->document->vcs_reference ?? null;
        
        $dueDate = null;
        if (!empty($this->document->due_date)) {
            $dueDate = $this->document->due_date instanceof \DateTimeInterface 
                ? $this->document->due_date->format('d/m/Y')
                : (string) $this->document->due_date;
        }

        // Amount calculation: minor (cents) vs major
        $totalAmount = 0.0;
        if (isset($this->document->total_minor)) {
            $totalAmount = $this->document->total_minor / 100;
        } elseif (isset($this->document->total_amount)) {
            $totalAmount = (float) $this->document->total_amount;
        }

        $recipientName = 'Klant';
        if ($customer) {
            $recipientName = $customer->contact_name 
                ?? $customer->name 
                ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) 
                ?: 'Klant';
        }

        return new Content(
            view: 'emails.invoices.send_document',
            with: [
                'document' => $this->document,
                'tenant' => $tenant,
                'customer' => $customer,
                'documentTitle' => $this->getDocumentTitle(),
                'documentNumber' => $number,
                'dueDate' => $dueDate,
                'structuredReference' => $structuredRef,
                'formattedTotal' => number_format($totalAmount, 2, ',', '.'),
                'paymentUrl' => $this->paymentUrl,
                'recipientName' => $recipientName,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        $pdfPath = $this->document->pdf_path ?? null;

        if ($pdfPath && Storage::exists($pdfPath)) {
            $number = $this->document->document_number ?? $this->document->invoice_number ?? 'document';
            $attachments[] = Attachment::fromStorage($pdfPath)
                ->as("{$number}.pdf")
                ->withMime('application/pdf');
        }

        return $attachments;
    }

    /**
     * Resolve document title in Dutch.
     */
    protected function getDocumentTitle(): string
    {
        $type = $this->document->type ?? 'invoice';

        return match ($type) {
            'quote', 'offerte' => 'Offerte',
            'credit_note' => 'Creditnota',
            default => 'Factuur',
        };
    }
}
