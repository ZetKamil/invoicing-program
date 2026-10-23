<?php

namespace App\Actions\Invoices;

use App\Mail\SendCommercialDocumentMail;
use App\Models\AuditLog;
use App\Models\CommercialDocument;
use App\Models\Communication;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SendCommercialDocumentAction
{
    public function __construct(
        protected GenerateInvoicePdfAction $generateInvoicePdfAction
    ) {}

    /**
     * Dispatch invoice or quote to customer with payment link, PDF attachment and communications log.
     *
     * @param  CommercialDocument|Invoice $document
     * @param  string|null                $recipientEmail
     * @param  string|null                $operatorUserId
     * @return CommercialDocument|Invoice
     */
    public function execute(
        CommercialDocument|Invoice $document,
        ?string $recipientEmail = null,
        ?string $operatorUserId = null
    ): CommercialDocument|Invoice {
        $document->loadMissing(['tenant', 'customer']);

        $tenant = $document->tenant;
        if (!$tenant) {
            throw new InvalidArgumentException("Document #{$document->id} heeft geen gekoppelde tenant.");
        }

        // 1. Ensure document has a secure public payment token for /pay/{token}
        if (empty($document->payment_token)) {
            $paymentToken = Str::random(40);
            $document->payment_token = $paymentToken;
            $document->saveQuietly();
        }

        // 2. Resolve PDF artifact
        if (empty($document->pdf_path) && $document instanceof Invoice) {
            try {
                $this->generateInvoicePdfAction->execute($document);
            } catch (\Throwable $e) {
                Log::warning("PDF generation failed during document send: {$e->getMessage()}", [
                    'document_id' => $document->id,
                ]);
            }
        }

        // 3. Resolve public payment URL (internal /pay/{token} portal or direct Stripe Checkout)
        $paymentUrl = $this->resolvePaymentUrl($document);

        // 4. Resolve recipient email
        $email = $recipientEmail ?: $document->customer?->email;
        if ($email) {
            Mail::to($email)->send(new SendCommercialDocumentMail($document, $paymentUrl));

            // 5. Record communication in database
            Communication::create([
                'id' => (string) Str::ulid(),
                'tenant_id' => $tenant->id,
                'type' => 'email',
                'subject' => "Factuur " . ($document->document_number ?? $document->invoice_number) . " verzonden",
                'body' => "Factuur " . ($document->document_number ?? $document->invoice_number) . " verzonden naar {$email} met online betaallink: {$paymentUrl}",
                'related_type' => get_class($document),
                'related_id' => $document->id,
                'sent_at' => now(),
            ]);
        } else {
            Log::info("Commercial document #{$document->id} has no recipient email; marked as sent without dispatching email.");
        }

        // 6. Update document status
        $document->status = 'sent';
        if (isset($document->sent_at)) {
            $document->sent_at = now();
        }
        $document->save();

        // 7. Audit Log
        AuditLog::create([
            'id' => (string) Str::ulid(),
            'tenant_id' => $tenant->id,
            'user_id' => $operatorUserId,
            'action' => 'document.sent',
            'auditable_type' => get_class($document),
            'auditable_id' => $document->id,
            'new_values' => [
                'recipient_email' => $email,
                'payment_url' => $paymentUrl,
                'status' => 'sent',
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'KMO-Flow Dispatcher',
            'created_at' => now(),
        ]);

        return $document;
    }

    /**
     * Resolve the public payment URL for this document.
     */
    protected function resolvePaymentUrl(CommercialDocument|Invoice $document): string
    {
        $token = $document->payment_token ?? $document->id;

        // If named route exists, use it; otherwise build default path
        try {
            return route('pay.portal', ['token' => $token]);
        } catch (\Throwable) {
            return url("/pay/{$token}");
        }
    }
}
