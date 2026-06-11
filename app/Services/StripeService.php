<?php

namespace App\Services;

use App\Models\Invoice;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService
{
    public function __construct()
    {
        // Use secret from config, or a dummy default for testing
        Stripe::setApiKey(config('services.stripe.secret', 'sk_test_dummy'));
    }

    /**
     * Create a Stripe Checkout Session for the given Invoice.
     */
    public function createCheckoutSessionForInvoice(Invoice $invoice)
    {
        // FINANCIAL PRECISION: BCMath cents conversion — never use round($amount * 100).
        // round() uses PHP float multiplication internally. On certain decimal values, IEEE 754
        // drift causes round(x * 100) to produce the wrong integer (off by 1 cent).
        // bcmul('12345.67', '100', 0) = '1234567' (exact), then cast to int.
        $amountInCents = (int) bcmul((string) $invoice->total_amount, '100', 0);
        
        $tenantName = $invoice->tenant->name ?? 'Logistics Provider';

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Invoice ' . $invoice->invoice_number . ' from ' . $tenantName,
                        'description' => 'Payment for services rendered.',
                    ],
                    'unit_amount' => $amountInCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('invoice.pay', ['invoice' => $invoice->id]) . '?success=true',
            'cancel_url' => route('invoice.pay', ['invoice' => $invoice->id]) . '?canceled=true',
            'metadata' => [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id,
            ],
            'customer_email' => $invoice->customer->email ?? null,
        ]);

        return redirect()->away($session->url);
    }

    /**
     * Create a Stripe Checkout Session for a new Tenant Subscription.
     */
    public function createCheckoutSessionForSubscription(string $registrationId, string $plan)
    {
        // Map internal plans to Stripe Price IDs (use dummy IDs for testing/development)
        $priceId = match(strtolower($plan)) {
            'start' => config('services.stripe.price_start', 'price_test_start'),
            'pro' => config('services.stripe.price_pro', 'price_test_pro'),
            default => config('services.stripe.price_start', 'price_test_start'),
        };

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('home') . '?subscription=success',
            'cancel_url' => route('register') . '?canceled=true',
            'metadata' => [
                'registration_id' => $registrationId,
            ],
            // Trial period for the Start plan, etc can be configured here
            'subscription_data' => [
                'metadata' => [
                    'registration_id' => $registrationId,
                ],
            ]
        ]);

        return redirect()->away($session->url);
    }
}
