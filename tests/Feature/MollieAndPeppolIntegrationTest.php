<?php

use App\Actions\Invoices\SendPeppolInvoiceAction;
use App\Actions\Tenants\ProvisionTenantAction;
use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MollieService;
use App\Services\Peppol\PeppolAccessPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('Caddy domain check authorizes active tenant custom domain and rejects unknown domains', function () {
    Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Desmet Transport',
        'slug' => 'desmet-transport',
        'custom_domain' => 'desmet-transport.be',
        'status' => 'active',
    ]);

    // Authorized custom domain
    $response = $this->get('/caddy/check-domain?domain=desmet-transport.be');
    $response->assertStatus(200);

    // Primary host / localhost
    $responseLocal = $this->get('/caddy/check-domain?domain=localhost');
    $responseLocal->assertStatus(200);

    // Unauthorized domain
    $responseUnknown = $this->get('/caddy/check-domain?domain=hacker-domain.com');
    $responseUnknown->assertStatus(404);

    // Missing domain parameter
    $responseMissing = $this->get('/caddy/check-domain');
    $responseMissing->assertStatus(400);
});

test('MollieService creates payment session with dynamic webhook and returns checkout url', function () {
    Http::fake([
        'https://api.mollie.com/v2/payments' => Http::response([
            'id' => 'tr_test_mollie_123',
            'status' => 'open',
            '_links' => [
                'checkout' => [
                    'href' => 'https://www.mollie.com/payscreen/select-method/tr_test_mollie_123',
                ],
            ],
        ], 201),
    ]);

    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Demo Transport',
        'slug' => 'demo-transport',
    ]);

    $customer = Customer::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'company_name' => 'Belgian Freight BV',
        'email' => 'freight@client.be',
    ]);

    $invoice = Invoice::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-2026-0001',
        'status' => InvoiceStatus::DRAFT,
        'total_amount' => '250.00',
        'subtotal' => '206.61',
        'tax_total' => '43.39',
        'due_date' => now()->addDays(14),
    ]);

    $mollieService = app(MollieService::class);
    $result = $mollieService->createPaymentForInvoice($invoice, 'test_mock_api_key_123');

    expect($result['payment_id'])->toBe('tr_test_mollie_123')
        ->and($result['checkout_url'])->toContain('mollie.com');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.mollie.com/v2/payments'
            && $request['amount']['value'] === '250.00'
            && $request['webhookUrl'] === route('mollie.webhook')
            && in_array('bancontact', $request['method']);
    });
});

test('Mollie webhook marks invoice as paid and logs communication', function () {
    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Demo Transport',
        'slug' => 'demo-transport',
    ]);

    $customer = Customer::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'company_name' => 'Belgian Freight BV',
        'email' => 'freight@client.be',
    ]);

    $invoice = Invoice::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-2026-0001',
        'status' => InvoiceStatus::SENT,
        'total_amount' => '250.00',
        'subtotal' => '206.61',
        'tax_total' => '43.39',
        'due_date' => now()->addDays(14),
    ]);

    Http::fake([
        'https://api.mollie.com/v2/payments/tr_test_mollie_123' => Http::response([
            'id' => 'tr_test_mollie_123',
            'status' => 'paid',
            'method' => 'bancontact',
            'amount' => ['value' => '250.00', 'currency' => 'EUR'],
            'metadata' => [
                'invoice_id' => $invoice->id,
            ],
        ], 200),
    ]);

    $response = $this->post('/webhook/mollie', [
        'id' => 'tr_test_mollie_123',
    ]);

    $response->assertStatus(200);

    $freshInvoice = $invoice->fresh();
    expect($freshInvoice->status)->toBe(InvoiceStatus::PAID)
        ->and($freshInvoice->paid_at)->not->toBeNull()
        ->and($freshInvoice->payment_method)->toBe('bancontact')
        ->and($freshInvoice->payment_reference)->toBe('tr_test_mollie_123');
});

test('SendPeppolInvoiceAction generates compliant UBL and dispatches via AS4 Access Point', function () {
    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Ziemkiewicz Logistics BV',
        'slug' => 'ziemkiewicz-logistics',
        'vat_number' => 'BE0123456789',
        'iban' => 'BE68539007547034',
        'bic' => 'GEBABEBX',
    ]);

    $customer = Customer::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'company_name' => 'Client Enterprise NV',
        'vat_number' => 'BE0987654321',
        'email' => 'accounts@client.be',
    ]);

    $invoice = Invoice::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-2026-8888',
        'status' => InvoiceStatus::DRAFT,
        'total_amount' => '1210.00',
        'subtotal' => '1000.00',
        'tax_total' => '210.00',
        'due_date' => now()->addDays(30),
    ]);

    $action = app(SendPeppolInvoiceAction::class);
    $result = $action->execute($invoice);

    expect($result['success'])->toBeTrue()
        ->and($result['transmission_id'])->toContain('AS4-')
        ->and(\Illuminate\Support\Facades\Storage::exists($result['xml_path']))->toBeTrue();

    $freshInvoice = $invoice->fresh();
    expect($freshInvoice->status)->toBe(InvoiceStatus::SENT)
        ->and($freshInvoice->peppol_status)->toBe('delivered')
        ->and($freshInvoice->peppol_transmission_id)->toBe($result['transmission_id']);
});

test('ProvisionTenantAction anti-cannibalization creates blank skeleton for Zelfbouwen and rich niche copy for Pro', function () {
    $action = app(ProvisionTenantAction::class);

    // 1. Zelfbouwen (€199 plan)
    $diyResult = $action->execute([
        'company_name' => 'DIY Salon BV',
        'admin_email' => 'salon@diy.be',
        'plan' => 'zelfbouwen',
        'niche' => 'transport', // User requested transport niche, but on DIY plan gets wireframe skeleton
    ]);

    expect($diyResult['homepage']->blocks)->toBeArray();
    $heroBlock = collect($diyResult['homepage']->blocks)->firstWhere('type', 'hero');
    // DIY plan does NOT get "Erkend Transporteur Benelux" or customized copywriting
    expect($heroBlock['data']['badge'])->toBe('Nieuwe Website')
        ->and($heroBlock['data']['badge'])->not->toBe('Erkend Transporteur Benelux')
        ->and($diyResult['homepage']->seo_meta['meta_title'])->toBe('DIY Salon BV');

    // 2. Pro / Turnkey (€1000+ plan)
    $proResult = $action->execute([
        'company_name' => 'Turnkey Transport NV',
        'admin_email' => 'contact@turnkey.be',
        'plan' => 'pro',
        'niche' => 'transport',
        'tagline' => 'Specialist in gekoeld transport Roeselare',
    ]);

    $proHero = collect($proResult['homepage']->blocks)->firstWhere('type', 'hero');
    // Pro plan gets full industry sales copywriting
    expect($proHero['data']['badge'])->toBe('Erkend Transporteur Benelux')
        ->and($proHero['data']['subtitle'])->toBe('Specialist in gekoeld transport Roeselare')
        ->and($proResult['homepage']->seo_meta['meta_title'])->toContain('Turnkey Transport NV | Officiële Website');
});
