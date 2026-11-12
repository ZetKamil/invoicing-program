<?php

namespace App\Livewire\Public;

use App\Models\Invoice;
use App\Services\MollieService;
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

        // Fallback for missing local webhooks: Check Stripe session synchronously
        $sessionId = request()->query('session_id');
        if ($sessionId && $invoice->status !== \App\Enums\InvoiceStatus::PAID) {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret', 'sk_test_dummy'));
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                
                if ($session->payment_status === 'paid') {
                    $invoice->update([
                        'status' => \App\Enums\InvoiceStatus::PAID,
                        'paid_at' => now(),
                        'stripe_payment_intent_id' => $session->payment_intent ?? null,
                    ]);
                    
                    app(\App\Actions\Communications\LogCommunicationAction::class)->execute(
                        $invoice->tenant_id,
                        'Invoice Mark As Paid via Stripe (Sync Fallback)',
                        $invoice,
                        \App\Enums\CommType::SYSTEM,
                        'Stripe Checkout Session ID: ' . $session->id
                    );
                }
            } catch (\Exception $e) {
                // Silently ignore Stripe API errors on public portal mount
            }
        }
    }

    public function payMollie(MollieService $mollieService)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($this->invoiceId);

        if (! $invoice->tenant_id || ! $invoice->tenant) {
            abort(403, 'Invalid invoice access.');
        }

        $invoice->load(['tenant', 'customer', 'items']);
        $payment = $mollieService->createPaymentForInvoice($invoice);

        return redirect()->away($payment['checkout_url']);
    }

    public function payStripe(StripeService $stripeService)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($this->invoiceId);

        if (! $invoice->tenant_id || ! $invoice->tenant) {
            abort(403, 'Invalid invoice access.');
        }

        $invoice->load(['tenant', 'customer', 'items']);

        return $stripeService->createCheckoutSessionForInvoice($invoice);
    }

    public function pay(MollieService $mollieService, StripeService $stripeService)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($this->invoiceId);
        $primary = $invoice->tenant?->settings['payment_gateways']['primary_provider'] ?? 'mollie';

        if ($primary === 'mollie' && $mollieService->isConfiguredForTenant($invoice->tenant)) {
            return $this->payMollie($mollieService);
        }

        return $this->payStripe($stripeService);
    }

    public function downloadPdf(\App\Actions\Invoices\GenerateInvoicePdfAction $generatePdfAction)
    {
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($this->invoiceId);
        
        // Use the action to generate or get the HTML/PDF content
        // Alternatively, since the action saves it to storage:
        $filename = "invoice-{$invoice->invoice_number}.pdf";
        $pdfPath = "tenants/{$invoice->tenant_id}/invoices/{$filename}";

        if (!\Illuminate\Support\Facades\Storage::exists($pdfPath)) {
            // Also check legacy path
            $legacyPath = "bedrijven/{$invoice->tenant_id}/invoices/{$filename}";
            if (\Illuminate\Support\Facades\Storage::exists($legacyPath)) {
                return \Illuminate\Support\Facades\Storage::download($legacyPath, $filename);
            }
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
