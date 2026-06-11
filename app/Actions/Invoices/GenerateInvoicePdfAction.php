<?php

namespace App\Actions\Invoices;

use App\Exceptions\UblGenerationException;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GenerateInvoicePdfAction
{
    public function __construct(
        protected GenerateUblXmlAction $generateUblXmlAction
    ) {}

    /**
     * Generate the invoice PDF (HTML placeholder) and attempt UBL 2.1 XML generation.
     *
     * Architecture Notes:
     *  - PDF and XML generation are DECOUPLED. A UBL XML failure does NOT abort PDF creation.
     *    The PDF is always generated and stored. The XML is attempted independently.
     *  - If XML generation fails (UblGenerationException), the error is logged and a warning
     *    is added to the invoice metadata, but the invoice remains actionable.
     *  - This follows the "Partial Success" pattern: critical user workflow (PDF) succeeds,
     *    compliance artifact (XML) failure is surfaced separately for operator review.
     *
     * @param  Invoice $invoice
     * @return Invoice  The updated invoice with pdf_path and optionally ubl_xml_path set.
     */
    public function execute(Invoice $invoice): Invoice
    {
        // Load all relationships needed for rendering both documents.
        $invoice->load(['bedrijf', 'customer', 'items']);

        // --- PDF Generation (always succeeds or throws non-UBL exception) ---
        $html     = View::make('invoices.pdf', compact('invoice'))->render();
        $filename = $invoice->invoice_number . '_' . time() . '.html';
        $pdfPath  = "bedrijven/{$invoice->bedrijf_id}/invoices/{$filename}";
        Storage::put($pdfPath, $html);

        // --- UBL 2.1 XML Generation (independent â€” failures are recoverable) ---
        // Wrapped in a dedicated try/catch so PDF success is never contingent on XML success.
        // A logistics company must always be able to send their invoice PDF to customers,
        // even if the Peppol XML compliance artifact temporarily fails.
        $xmlPath = null;
        try {
            $xmlPath = $this->generateUblXmlAction->execute($invoice);
        } catch (UblGenerationException $e) {
            // Log the failure with structured context for operator debugging.
            // Do NOT re-throw â€” PDF generation has already succeeded at this point.
            Log::warning('UBL XML generation failed during invoice finalization. PDF was saved successfully.', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'ubl_error'      => $e->getMessage(),
            ]);
        }

        // Persist document path references.
        // ubl_xml_path remains null if XML generation failed â€” surfaces in admin UI.
        $invoice->update([
            'ubl_xml_path' => $xmlPath,
        ]);

        return $invoice;
    }
}
