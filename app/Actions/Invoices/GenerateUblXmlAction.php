<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GenerateUblXmlAction
{
    /**
     * Generate Peppol-compatible UBL 2.1 XML for the Invoice.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function execute(Invoice $invoice): string
    {
        // Render the XML view without any formatting artifacts
        $xml = View::make('invoices.ubl', compact('invoice'))->render();

        // Generate a path scoped to the tenant
        $filename = $invoice->invoice_number . '.xml';
        $path = "tenants/{$invoice->tenant_id}/invoices/{$filename}";

        // Save to secure local storage
        Storage::put($path, trim($xml));

        return $path;
    }
}
