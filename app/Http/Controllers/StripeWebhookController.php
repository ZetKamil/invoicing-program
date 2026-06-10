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

        // Dispatch job for asynchronous processing
        \App\Jobs\ProcessStripeWebhookJob::dispatch($event);

        return response()->json(['status' => 'success'], 200);
    }
}
