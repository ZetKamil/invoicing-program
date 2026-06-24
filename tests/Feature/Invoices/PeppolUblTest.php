<?php

use App\Actions\Invoices\GenerateUblXmlAction;
use App\Models\Bedrijf;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Enums\InvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('generate ubl action produces valid xml', function () {
    Storage::fake('local');
    
    $bedrijf = Bedrijf::create([
        'name' => 'Transport Co UBL', 
        'slug' => 'transport-co-ubl',
        'vat_number' => 'NL123456789B01',
        'kvk_number' => '12345678',
        'address' => 'Main St 1',
        'city' => 'Amsterdam',
        'zip_code' => '1000AA',
        'country' => 'NL',
    ]);

    $lead = Lead::create([
        'bedrijf_id' => $bedrijf->id,
        'company_name' => 'Client Corp UBL',
        'contact_person' => 'Jane Doe',
        'vat_number' => 'BE987654321',
        'address' => 'South St 2',
        'city' => 'Antwerp',
        'zip_code' => '2000',
        'country' => 'BE',
    ]);

    $invoice = Invoice::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_type' => Lead::class,
        'customer_id' => $lead->id,
        'invoice_number' => 'INV-UBL-001',
        'due_date' => now()->addDays(14),
        'status' => InvoiceStatus::DRAFT,
        'is_reverse_charge' => false,
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Standard Freight',
        'quantity' => 1,
        'unit_price' => 1000,
        'tax_rate' => 21.00,
    ]);

    $action = app(GenerateUblXmlAction::class);
    $path = $action->execute($invoice);

    expect($path)->toBeString()->toEndWith('.xml')
        ->and(Storage::disk('local')->exists($path))->toBeTrue();

    $xmlContent = Storage::disk('local')->get($path);
    expect($xmlContent)->toContain('<cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>')
        ->toContain('<cbc:Percent>21.00</cbc:Percent>')
        ->toContain('<cbc:ID>S</cbc:ID>'); // Standard tax category
});

test('ubl xml handles reverse charge logic correctly', function () {
    Storage::fake('local');
    
    $bedrijf = Bedrijf::create([
        'name' => 'Transport Co RC', 
        'slug' => 'transport-co-rc',
        'vat_number' => 'NL123456789B01',
        'country' => 'NL',
    ]);

    $lead = Lead::create([
        'bedrijf_id' => $bedrijf->id,
        'company_name' => 'Client Corp RC',
        'contact_person' => 'Jane Doe',
        'vat_number' => 'BE987654321',
        'country' => 'BE',
    ]);

    $invoice = Invoice::create([
        'bedrijf_id' => $bedrijf->id,
        'customer_type' => Lead::class,
        'customer_id' => $lead->id,
        'invoice_number' => 'INV-RC-001',
        'due_date' => now()->addDays(14),
        'status' => InvoiceStatus::DRAFT,
        'is_reverse_charge' => true,
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'description' => 'Reverse Charge Freight',
        'quantity' => 1,
        'unit_price' => 1000,
        // tax rate is 0 internally for RC, but observer would handle it. For test, we set it manually:
        'tax_rate' => 0.00,
    ]);

    $action = app(GenerateUblXmlAction::class);
    $path = $action->execute($invoice);

    $xmlContent = Storage::disk('local')->get($path);
    
    expect($xmlContent)->toBeString();
    
    // Check for Reverse Charge Tax Exemption Reason Code and AE TaxCategory
    test()->assertStringContainsString('<cbc:TaxExemptionReasonCode>AE</cbc:TaxExemptionReasonCode>', $xmlContent);
    test()->assertStringContainsString('<cbc:ID>AE</cbc:ID>', $xmlContent);
    test()->assertStringContainsString('<cbc:Percent>0.00</cbc:Percent>', $xmlContent);
});
