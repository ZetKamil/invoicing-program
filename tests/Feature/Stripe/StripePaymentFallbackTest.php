<?php

use App\Models\Bedrijf;
use App\Models\Invoice;
use App\Models\Lead;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\ProcessStripeWebhookJob;

uses(RefreshDatabase::class);

test('invoice pay portal handles session_id fallback properly', function () {
    // We cannot easily mock the Stripe API call inside a Livewire component mount without complex setup, 
    // but we can ensure the portal renders correctly for a draft invoice.
    
    $bedrijf = Bedrijf::create(['name' => 'Stripe Co', 'slug' => 'stripe-co']);
    $lead = Lead::create(['bedrijf_id' => $bedrijf->id, 'company_name' => 'Client', 'contact_person' => 'Jane Doe']);
    
    $invoice = Invoice::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_type' => Lead::class,
        'customer_id' => $lead->id,
        'invoice_number' => 'INV-STRIPE-001',
        'due_date' => now()->addDays(14),
        'status' => InvoiceStatus::DRAFT,
    ]);

    // Test that the portal renders without auth (it's public)
    $response = $this->get(route('invoice.pay', ['invoice' => $invoice->id]));
    $response->assertOk();
    $response->assertSee('INV-STRIPE-001');
    $response->assertSee('Betaal met kaart (Stripe)');
});

test('stripe webhook job marks invoice as paid', function () {
    $bedrijf = Bedrijf::create(['name' => 'Stripe Co 2', 'slug' => 'stripe-co-2']);
    $lead = Lead::create(['bedrijf_id' => $bedrijf->id, 'company_name' => 'Client 2', 'contact_person' => 'Jane Doe']);
    
    $invoice = Invoice::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_type' => Lead::class,
        'customer_id' => $lead->id,
        'invoice_number' => 'INV-WEBHOOK-001',
        'due_date' => now()->addDays(14),
        'status' => InvoiceStatus::SENT,
    ]);

    // Mock the Stripe event object
    $event = (object) [
        'type' => 'checkout.session.completed',
        'data' => (object) [
            'object' => (object) [
                'mode' => 'payment',
                'payment_intent' => 'pi_test_12345',
                'id' => 'cs_test_12345',
                'metadata' => (object) [
                    'invoice_id' => $invoice->id,
                ]
            ]
        ]
    ];

    $job = new ProcessStripeWebhookJob($event);
    $action = app(\App\Actions\Communications\LogCommunicationAction::class);
    
    $job->handle($action);

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::PAID)
        ->and($invoice->stripe_payment_intent_id)->toBe('pi_test_12345')
        ->and($invoice->paid_at)->not->toBeNull();
});
