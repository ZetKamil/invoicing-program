<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GenerateInvoicePdfAction
{
    /**
     * Generate PDF (or HTML placeholder) for the Invoice.
     */
    public function execute(Invoice $invoice): Invoice
    {
        // Load relationships needed for the view
        $invoice->load(['customer', 'items']);

        // Render the HTML view
        $html = View::make('invoices.pdf', compact('invoice'))->render();

        // Generate a path scoped to the tenant
        $filename = $invoice->invoice_number . '_' . time() . '.html';
        $path = "tenants/{$invoice->tenant_id}/invoices/{$filename}";

        // Save to secure local storage
        Storage::put($path, $html);

        // Update the document reference metadata
        $invoice->update([
            'ubl_xml_path' => $path, // Reusing this field as a document reference placeholder
        ]);

        return $invoice;
    }
}
