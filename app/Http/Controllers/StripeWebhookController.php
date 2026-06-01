<?php

namespace App\Http\Controllers;

use App\Actions\Communications\LogCommunicationAction;
use App\Enums\CommType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    /**
     * Handle public Stripe webhook events.
     */
    public function handleWebhook(Request $request, LogCommunicationAction $logCommunicationAction)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret', 'whsec_dummy');

        try {
            // Verify signature using the Stripe SDK if secret is set
            if ($endpointSecret !== 'whsec_dummy') {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sigHeader, $endpointSecret
                );
            } else {
                // For local testing without valid signatures
                $event = json_decode($payload);
                $event = (object) [
                    'type' => $event->type ?? '',
                    'data' => (object) ['object' => $event->data->object ?? []],
                ];
            }
            
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the checkout.session.completed event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Extract invoice ID from metadata
            $invoiceId = $session->metadata->invoice_id ?? null;

            if ($invoiceId) {
                // Bypass global scopes since the webhook is not authenticated as a user
                $invoice = Invoice::withoutGlobalScopes()->find($invoiceId);

                if ($invoice && $invoice->status !== InvoiceStatus::PAID) {
                    $invoice->update([
                        'status' => InvoiceStatus::PAID,
                        'paid_at' => now(),
                        'stripe_payment_intent_id' => $session->payment_intent ?? null,
                    ]);

                    // Trigger communication log
                    $logCommunicationAction->execute(
                        $invoice->tenant_id,
                        'Invoice Mark As Paid via Stripe',
                        $invoice,
                        CommType::SYSTEM,
                        'Stripe Checkout Session ID: ' . $session->id
                    );

                    Log::info("Invoice {$invoice->invoice_number} marked as paid via Stripe webhook.");
                }
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
