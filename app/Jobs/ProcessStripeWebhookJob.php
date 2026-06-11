<?php

namespace App\Jobs;

use App\Actions\Communications\LogCommunicationAction;
use App\Enums\CommType;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Bedrijf;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessStripeWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public object $event
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LogCommunicationAction $logCommunicationAction): void
    {
        $event = $this->event;

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->mode === 'subscription') {
                $registrationId = $session->metadata->registration_id ?? null;
                
                if ($registrationId) {
                    $registrationData = Cache::get("registration_{$registrationId}");
                    
                    if ($registrationData) {
                        DB::transaction(function () use ($registrationData, $session, $registrationId) {
                            $bedrijf = Bedrijf::create([
                                'name' => $registrationData['company_name'],
                                'slug' => Str::slug($registrationData['company_name']) . '-' . strtolower(Str::random(4)),
                                'stripe_id' => $session->customer,
                                'stripe_subscription_id' => $session->subscription,
                                'stripe_subscription_status' => 'active',
                            ]);

                            User::create([
                                'bedrijf_id' => $bedrijf->id,
                                'name' => 'Admin',
                                'email' => $registrationData['email'],
                                'password' => $registrationData['password'],
                                'role' => \App\Enums\UserRole::ADMIN,
                            ]);

                            Log::info("Provisioned new bedrijf: {$bedrijf->name} via SaaS Webhook.");
                            Cache::forget("registration_{$registrationId}");
                        });
                    }
                }
            } else {
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
                            $invoice->bedrijf_id,
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
            $bedrijf = Bedrijf::where('stripe_subscription_id', $subscription->id)->first();
            
            if ($bedrijf) {
                $bedrijf->update([
                    'stripe_subscription_status' => $subscription->status,
                ]);
                Log::info("Updated subscription status for bedrijf: {$bedrijf->name} to {$subscription->status}.");
            }
        }
    }
}
