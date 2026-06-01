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
        // Convert to cents (Stripe requires integer amounts)
        $amountInCents = (int) round($invoice->total_amount * 100);
        
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
}
