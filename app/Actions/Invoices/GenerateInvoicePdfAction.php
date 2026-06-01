<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GenerateInvoicePdfAction
{
    public function __construct(
        protected GenerateUblXmlAction $generateUblXmlAction
    ) {}

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

        // Generate UBL 2.1 XML
        $xmlPath = $this->generateUblXmlAction->execute($invoice);

        // Update the document reference metadata
        $invoice->update([
            'ubl_xml_path' => $xmlPath,
        ]);

        return $invoice;
    }
}
