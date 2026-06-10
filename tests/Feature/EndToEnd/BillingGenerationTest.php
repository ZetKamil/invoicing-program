<?php

use App\Actions\Invoices\CreateInvoiceFromQuoteAction;
use App\Actions\Invoices\GenerateInvoicePdfAction;
use App\Actions\Invoices\GenerateUblXmlAction;
use App\Enums\InvoiceStatus;
use App\Enums\QuoteStatus;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Quote;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('quote to invoice pipeline works with accurate taxes and file generation', function () {
    Storage::fake('local');

    $tenant = Tenant::create(['name' => 'Billing Corp', 'slug' => 'billing-corp']);
    
    $lead = Lead::create([
        'tenant_id' => $tenant->id,
        'company_name' => 'Tech Client',
        'contact_person' => 'Tech Contact',
        'email' => 'tech@client.com',
    ]);

    $quote = Quote::create([
        'tenant_id' => $tenant->id,
        'lead_id' => $lead->id,
        'quote_number' => 'Q-2026-001',
        'total_amount' => 1000.00, // exact 1000 subtotal
        'valid_until' => now()->addDays(30),
        'status' => QuoteStatus::ACCEPTED,
    ]);

    // 1. Create Invoice from Quote
    $invoiceAction = new CreateInvoiceFromQuoteAction();
    $invoice = $invoiceAction->execute($quote);

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->subtotal)->toEqual(1000.00)
        // 21% tax of 1000 is 210
        ->and($invoice->tax_total)->toEqual(210.00)
        ->and($invoice->total_amount)->toEqual(1210.00)
        ->and($invoice->status)->toBe(InvoiceStatus::DRAFT)
        ->and($invoice->tenant_id)->toBe($tenant->id);

    // 2. Test File Generation
    $pdfAction = new GenerateInvoicePdfAction(new GenerateUblXmlAction());
    $pdfAction->execute($invoice);

    // Refresh model to get ubl_xml_path
    $invoice->refresh();

    // Verify metadata was updated
    expect($invoice->ubl_xml_path)->not->toBeNull();

    // Assert files exist on fake disk
    $files = Storage::disk('local')->allFiles("tenants/{$tenant->id}/invoices");
    expect(count($files))->toBeGreaterThanOrEqual(2); // Should have PDF (HTML placeholder) and XML

    $hasXml = collect($files)->contains(fn ($file) => str_ends_with($file, '.xml'));
    $hasHtml = collect($files)->contains(fn ($file) => str_ends_with($file, '.html'));

    expect($hasXml)->toBeTrue();
    expect($hasHtml)->toBeTrue();
});
