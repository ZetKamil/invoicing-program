<?php

use App\Actions\Invoices\CreateInvoiceFromQuoteAction;
use App\Actions\Invoices\GenerateInvoicePdfAction;
use App\Models\Tenant;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Enums\InvoiceStatus;
use App\Enums\QuoteStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('create invoice from quote action transfers logistics fields correctly', function () {
    $tenant = Tenant::create(['name' => 'Transport Co', 'slug' => 'transport-co']);
    
    $customer = \App\Models\Customer::create([
        'tenant_id' => $tenant->id,
        'company_name' => 'Client Corp',
        'contact_person' => 'Jane Doe',
    ]);

    $quote = Quote::create([
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
        'quote_number' => 'Q-123',
        'status' => QuoteStatus::ACCEPTED,
        'valid_until' => now()->addDays(7),
        'total_amount' => 1000,
        // Logistics fields
        'loading_address' => 'Amsterdam HQ',
        'delivery_address' => 'Rotterdam Port',
        'cargo_weight_kg' => 20000,
        'pallet_count' => 33,
        'trailer_type' => 'Koelwagen',
        'incoterms' => 'DAP',
    ]);

    QuoteItem::create([
        'quote_id' => $quote->id,
        'description' => 'Freight from AMS to ROT',
        'quantity' => 1,
        'unit_price' => 1000,
        'tax_rate' => 21,
    ]);

    $action = new CreateInvoiceFromQuoteAction();
    $invoice = $action->execute($quote);

    expect($invoice->quote_id)->toBe($quote->id)
        ->and($invoice->customer_id)->toBe($customer->id)
        ->and($invoice->status)->toBe(InvoiceStatus::DRAFT)
        ->and($invoice->loading_address)->toBe('Amsterdam HQ')
        ->and($invoice->delivery_address)->toBe('Rotterdam Port')
        ->and($invoice->cargo_weight_kg)->toBe(20000)
        ->and($invoice->pallet_count)->toBe(33)
        ->and($invoice->trailer_type)->toBe('Koelwagen')
        ->and($invoice->incoterms)->toBe('DAP')
        ->and($invoice->items()->count())->toBe(1);
});

test('generate invoice pdf action works without throwing errors', function () {
    Storage::fake('public');
    
    $tenant = Tenant::create(['name' => 'Transport Co', 'slug' => 'transport-co2']);
    $lead = Lead::create(['tenant_id' => $tenant->id, 'company_name' => 'Client Corp', 'contact_person' => 'Jane Doe']);

    $invoice = \App\Models\Invoice::create([
        'tenant_id' => $tenant->id,
        'customer_type' => Lead::class,
        'customer_id' => $lead->id,
        'invoice_number' => 'INV-999',
        'due_date' => now()->addDays(14),
        'status' => InvoiceStatus::DRAFT,
        'cmr_number' => 'CMR-PDF',
    ]);

    $action = app(GenerateInvoicePdfAction::class);
    $action->execute($invoice);

    $files = Storage::allFiles("tenants/{$tenant->id}/invoices");
    $htmlFile = collect($files)->first(fn($f) => str_ends_with($f, '.html'));
    expect($htmlFile)->not->toBeNull();
    
    $html = Storage::get($htmlFile);

    expect($html)->toBeString()
        ->and($html)->toContain('INV-999')
        ->and($html)->toContain('CMR-PDF');
});
