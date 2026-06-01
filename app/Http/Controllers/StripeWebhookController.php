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

            if ($session->mode === 'subscription') {
                $registrationId = $session->metadata->registration_id ?? null;
                
                if ($registrationId) {
                    $registrationData = \Illuminate\Support\Facades\Cache::get("registration_{$registrationId}");
                    
                    if ($registrationData) {
                        // Provision Tenant
                        $tenant = \App\Models\Tenant::create([
                            'name' => $registrationData['company_name'],
                            'slug' => \Illuminate\Support\Str::slug($registrationData['company_name']) . '-' . strtolower(\Illuminate\Support\Str::random(4)),
                            'stripe_id' => $session->customer,
                            'stripe_subscription_id' => $session->subscription,
                            'stripe_subscription_status' => 'active',
                        ]);

                        // Create Admin User
                        $user = \App\Models\User::create([
                            'tenant_id' => $tenant->id,
                            'name' => 'Admin',
                            'email' => $registrationData['email'],
                            'password' => $registrationData['password'], // Already hashed
                            'role' => \App\Enums\UserRole::ADMIN,
                        ]);

                        Log::info("Provisioned new tenant: {$tenant->name} via SaaS Webhook.");
                        \Illuminate\Support\Facades\Cache::forget("registration_{$registrationId}");
                    }
            } else {
                // Handle standard one-off invoice payments
                $invoiceId = $session->metadata->invoice_id ?? null;

                if ($invoiceId) {
                    $invoice = Invoice::withoutGlobalScopes()->find($invoiceId);

                    if ($invoice && $invoice->status !== InvoiceStatus::PAID) {
                        $invoice->update([
                            'status' => InvoiceStatus::PAID,
                            'paid_at' => now(),
                            'stripe_payment_intent_id' => $session->payment_intent ?? null,
                        ]);

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
        } elseif (in_array($event->type, ['customer.subscription.updated', 'customer.subscription.deleted'])) {
            $subscription = $event->data->object;
            $tenant = \App\Models\Tenant::where('stripe_subscription_id', $subscription->id)->first();
            
            if ($tenant) {
                $tenant->update([
                    'stripe_subscription_status' => $subscription->status,
                ]);
                Log::info("Updated subscription status for tenant: {$tenant->name} to {$subscription->status}.");
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
