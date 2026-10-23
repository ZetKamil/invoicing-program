<?php

use App\Actions\Tenants\ProvisionTenantAction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Tenant;
use App\Support\TemplateBlocks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('Public homepage renders default presentation or dynamic tenant blocks', function () {
    // 1. Un-provisioned / default landing
    $response = $this->get('/');
    $response->assertStatus(200);

    // 2. Provision a tenant with Niche Blocks
    $action = app(ProvisionTenantAction::class);
    $result = $action->execute([
        'company_name' => 'Flemish Cat Sanctuary BV',
        'niche' => TemplateBlocks::NICHE_BREEDING,
        'plan' => 'pro',
        'admin_name' => 'Katrijn Smeets',
        'admin_email' => 'katrijn@catsanctuary.be',
        'tagline' => 'Exclusieve en kerngezonde raskatten',
    ]);

    $tenant = $result['tenant'];

    // 3. Visit with ?tenant=slug
    $tenantResponse = $this->get("/?tenant={$tenant->slug}");
    $tenantResponse->assertStatus(200)
        ->assertSee('Flemish Cat Sanctuary BV')
        ->assertSee('Exclusieve en kerngezonde raskatten')
        ->assertSee('Erkende Fokkerij met Stamboom');
});

test('Public contact form creates scoped Lead with honeypot bot defense', function () {
    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Transport De Smet',
        'slug' => 'transport-de-smet',
        'plan' => 'scale',
    ]);

    // 1. Valid submission
    $response = $this->post('/contact/submit', [
        'tenant_id' => $tenant->id,
        'name' => 'Marc Dupont',
        'email' => 'marc@dupont-industries.be',
        'phone' => '+32470123456',
        'message' => 'Wij zoeken 4 volle vrachten per week naar Rotterdam.',
        'website_hp' => '', // Empty honeypot
    ]);

    $response->assertSessionHas('contact_success');

    $lead = Lead::where('tenant_id', $tenant->id)->where('email', 'marc@dupont-industries.be')->first();
    expect($lead)->not->toBeNull()
        ->and($lead->name)->toBe('Marc Dupont')
        ->and($lead->source)->toBe('contact_form')
        ->and($lead->notes)->toContain('Rotterdam');

    // 2. Bot submission with filled honeypot (should not create duplicate lead)
    $botResponse = $this->post('/contact/submit', [
        'tenant_id' => $tenant->id,
        'name' => 'Bot Spammer',
        'email' => 'spam@bot.com',
        'message' => 'Spam content',
        'website_hp' => 'I am a spam bot',
    ]);

    $botResponse->assertSessionHas('contact_success');
    expect(Lead::where('email', 'spam@bot.com')->exists())->toBeFalse();
});

test('Public payment portal loads invoice securely without login', function () {
    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Ziemkiewicz Digital Agency',
        'slug' => 'ziemkiewicz-digital',
        'plan' => 'scale',
    ]);

    $customer = Customer::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'company_name' => 'Client BV',
        'email' => 'client@client.be',
    ]);

    $invoice = Invoice::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-2026-0042',
        'total_amount' => 150.00,
        'status' => 'sent',
        'payment_token' => 'secure_payment_token_abcdef123456',
    ]);

    $response = $this->get("/pay/{$invoice->id}");
    $response->assertStatus(200)
        ->assertSee('INV-2026-0042')
        ->assertSee('150');
});
