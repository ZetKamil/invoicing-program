<?php

namespace App\Livewire\Public;

use App\Models\Invoice;
use App\Services\StripeService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.layouts.app')]
class InvoicePayPortal extends Component
{
    /**
     * The ULID of the invoice to display.
     *
     * SECURITY: This property is marked #[Locked] to prevent Livewire Model Substitution Attacks.
     *
     * WITHOUT #[Locked]: Livewire serializes the property value into a signed browser snapshot.
     * An attacker using DevTools can intercept the Livewire POST request and swap this ULID
     * to any other invoice ID. On hydration, Livewire would re-fetch that invoice and pass it
     * to pay() — creating a Stripe session for someone else's invoice.
     *
     * WITH #[Locked]: Any attempt to modify this value from the browser triggers a
     * "CannotBindToComponentDataWithoutValidation" exception — Livewire refuses to hydrate
     * the tampered request entirely.
     *
     * We store the ID (string) rather than the Eloquent model (public Invoice $invoice)
     * because Livewire serializes models into the DOM snapshot. A string ULID has minimal
     * attack surface — there is nothing to tamper with structurally.
     */
    #[Locked]
    public string $invoiceId = '';

    public function mount(Invoice $invoice): void
    {
        // Store only the ID — never the full model — in component state.
        $this->invoiceId = $invoice->id;
    }

    public function pay(StripeService $stripeService)
    {
        // SERVER-SIDE RE-VERIFICATION: Re-fetch the invoice fresh from the database
        // using the locked ID. This guarantees we always operate on the authoritative
        // server state, not on any potentially stale or tampered browser snapshot.
        //
        // We use withoutGlobalScopes() because this is a PUBLIC portal — the customer
        // accessing their invoice is not authenticated as a tenant user.
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($this->invoiceId);

        // TENANT OWNERSHIP GUARD: Even though the ULID is locked, we add an explicit
        // check to ensure the invoice belongs to a valid, active tenant.
        // This is the "Defense in Depth" principle applied to the payment pipeline.
        if (! $invoice->tenant_id || ! $invoice->tenant) {
            abort(403, 'Invalid invoice access.');
        }

        // Reload relationships for Stripe session metadata
        $invoice->load(['tenant', 'customer', 'items']);

        return $stripeService->createCheckoutSessionForInvoice($invoice);
    }

    public function downloadPdf(\App\Actions\Invoices\GenerateInvoicePdfAction $generatePdfAction)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($this->invoiceId);
        
        // Use the action to generate or get the HTML/PDF content
        // Alternatively, since the action saves it to storage:
        $filename = "invoice-{$invoice->invoice_number}.pdf";
        $pdfPath = "tenants/{$invoice->tenant_id}/invoices/{$filename}";

        if (!\Illuminate\Support\Facades\Storage::exists($pdfPath)) {
            // If it doesn't exist yet, we can try to generate it, or just return an error
            try {
                $generatePdfAction->execute($invoice);
            } catch (\Exception $e) {
                // Ignore UBL exceptions if any
            }
        }

        if (\Illuminate\Support\Facades\Storage::exists($pdfPath)) {
            return \Illuminate\Support\Facades\Storage::download($pdfPath, $filename);
        }

        $this->js("alert('PDF is not available for this invoice yet.');");
    }

    public function render()
    {
        // Re-fetch fresh data on every render — never trust stale hydrated model state.
        $invoice = Invoice::withoutGlobalScopes()
            ->with(['tenant', 'customer', 'items'])
            ->findOrFail($this->invoiceId);

        return view('livewire.public.invoice-pay-portal', [
            'invoice' => $invoice,
        ]);
    }
}
