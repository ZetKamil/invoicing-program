<?php

namespace App\Actions\Invoices;

use App\Exceptions\UblGenerationException;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GenerateUblXmlAction
{
    /**
     * Generate Peppol-compatible UBL 2.1 XML for the Invoice.
     *
     * Architecture Notes:
     *  - Uses Blade rendering to build the XML document string (avoids raw string concatenation
     *    and benefits from Blade's auto-escaping for XSS prevention in XML values).
     *  - Validates the rendered output with PHP's DOMDocument parser before storage.
     *    If the document is not well-formed XML, a UblGenerationException is thrown BEFORE
     *    writing anything to disk â€” preventing a corrupt XML file from being stored.
     *  - Throws UblGenerationException (not generic Exception) so callers can catch
     *    Peppol-specific failures independently from other errors.
     *
     * @param  Invoice $invoice  Must have 'bedrijf', 'customer', and 'items' loaded.
     * @return string            The storage path of the saved XML file.
     * @throws UblGenerationException
     */
    public function execute(Invoice $invoice): string
    {
        // Ensure all required relationships are loaded before Blade rendering.
        // Missing relationships produce null values that render as 'UNKNOWN' in the XML,
        // which Peppol Access Points may reject as invalid party identifiers.
        $invoice->loadMissing(['bedrijf', 'customer', 'items']);

        // Step 1: Render the UBL Blade template into an XML string.
        $xml = View::make('invoices.ubl', compact('invoice'))->render();

        // Step 2: Validate XML well-formedness using PHP's DOMDocument.
        // This catches Blade rendering artifacts (e.g. stray HTML entities, unescaped
        // special characters) that produce malformed XML before it reaches the Access Point.
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadXML(trim($xml));

        if (! $loaded) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $firstError = ! empty($errors) ? $errors[0]->message : 'Unknown XML parse error';

            Log::error('UBL XML generation failed â€” malformed XML output', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'xml_error'      => $firstError,
            ]);

            throw UblGenerationException::malformedXml($firstError);
        }

        libxml_clear_errors();

        // Step 3: Write to bedrijf-isolated storage.
        $filename = $invoice->invoice_number . '.xml';
        $path     = "bedrijven/{$invoice->bedrijf_id}/invoices/{$filename}";

        try {
            Storage::put($path, trim($xml));
        } catch (\Throwable $e) {
            Log::error('UBL XML storage write failed', [
                'invoice_id' => $invoice->id,
                'path'       => $path,
                'error'      => $e->getMessage(),
            ]);

            throw UblGenerationException::storageFailure($path, $e);
        }

        Log::info('UBL 2.1 XML generated successfully', [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'path'           => $path,
        ]);

        return $path;
    }
}
