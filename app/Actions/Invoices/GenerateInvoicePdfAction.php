<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;

class GenerateInvoicePdfAction
{
    /**
     * Execute the action to generate an invoice PDF.
     */
    public function execute(Invoice $invoice): string
    {
        // Placeholder for PDF generation logic (e.g. using Browsershot or DomPDF)
        // For now, return a mock path
        return "invoices/{$invoice->invoice_number}.pdf";
    }
}
