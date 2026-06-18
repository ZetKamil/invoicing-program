<?php

use App\Actions\Invoices\CreateInvoiceFromQuoteAction;
use App\Actions\Invoices\GenerateInvoicePdfAction;
use App\Models\Bedrijf;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Enums\InvoiceStatus;
use App\Enums\QuoteStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('create invoice from quote action transfers logistics fields correctly', function () {
    $bedrijf = Bedrijf::create(['name' => 'Transport Co', 'slug' => 'transport-co']);
    
    $lead = Lead::create([
        'bedrijf_id' => $bedrijf->id,
        'company_name' => 'Client Corp',
    ]);

    $quote = Quote::create([
        'bedrijf_id' => $bedrijf->id,
        'lead_id' => $lead->id,
        'quote_number' => 'Q-123',
        'status' => QuoteStatus::ACCEPTED,
        'valid_until' => now()->addDays(7),
        'total_amount' => 1000,
        // Logistics fields
        'cmr_number' => 'CMR-001',
        'truck_license_plate' => 'AA-123-BB',
        'trailer_license_plate' => 'TR-456-CC',
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
        ->and($invoice->customer_id)->toBe($lead->id)
        ->and($invoice->status)->toBe(InvoiceStatus::DRAFT)
        ->and($invoice->cmr_number)->toBe('CMR-001')
        ->and($invoice->truck_license_plate)->toBe('AA-123-BB')
        ->and($invoice->trailer_license_plate)->toBe('TR-456-CC')
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
    
    $bedrijf = Bedrijf::create(['name' => 'Transport Co', 'slug' => 'transport-co2']);
    $lead = Lead::create(['bedrijf_id' => $bedrijf->id, 'company_name' => 'Client Corp']);

    $invoice = \App\Models\Invoice::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_type' => Lead::class,
        'customer_id' => $lead->id,
        'invoice_number' => 'INV-999',
        'due_date' => now()->addDays(14),
        'status' => InvoiceStatus::DRAFT,
        'cmr_number' => 'CMR-PDF',
    ]);

    $action = app(GenerateInvoicePdfAction::class);
    $html = $action->execute($invoice);

    expect($html)->toBeString()
        ->and($html)->toContain('INV-999')
        ->and($html)->toContain('CMR-PDF');
});
