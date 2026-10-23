<?php

use App\Actions\Invoices\SendCommercialDocumentAction;
use App\Actions\Pages\BuildPageBlocksAction;
use App\Actions\Tenants\ProvisionTenantAction;
use App\Mail\SendCommercialDocumentMail;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CommercialDocument;
use App\Models\Communication;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Page;
use App\Models\TaxRate;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Ai\AgentToolsSchema;
use App\Support\TemplateBlocks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('TemplateBlocks generates valid block structures for all Flemish niches', function () {
    $niches = [
        TemplateBlocks::NICHE_TRANSPORT,
        TemplateBlocks::NICHE_BREEDING,
        TemplateBlocks::NICHE_CRAFTSMAN,
        TemplateBlocks::NICHE_BLANK,
    ];

    foreach ($niches as $niche) {
        $blocks = TemplateBlocks::getForNiche($niche, 'Test Bedrijf BV');
        expect(is_array($blocks))->toBeTrue()
            ->and(count($blocks))->toBeGreaterThan(1)
            ->and(TemplateBlocks::validateBlocks($blocks))->toBeTrue();

        $heroBlock = $blocks[0];
        expect($heroBlock['type'])->toBe('hero')
            ->and($heroBlock['data']['title'])->toBe('Test Bedrijf BV');
    }
});

test('ProvisionTenantAction atomically provisions a complete company environment', function () {
    $action = app(ProvisionTenantAction::class);

    $result = $action->execute([
        'company_name' => 'Vermeulen Transport BV',
        'niche' => TemplateBlocks::NICHE_TRANSPORT,
        'plan' => 'pro',
        'admin_name' => 'Luc Vermeulen',
        'admin_email' => 'luc@vermeulentransport.be',
        'vat_number' => 'BE0987654321',
        'iban' => 'BE68539007547034',
        'bic' => 'GEBABEBB',
        'tagline' => 'Internationaal koeltransport',
    ]);

    $tenant = $result['tenant'];
    $user = $result['user'];
    $homepage = $result['homepage'];

    // 1. Tenant record
    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->name)->toBe('Vermeulen Transport BV')
        ->and($tenant->slug)->toBe('vermeulen-transport-bv')
        ->and($tenant->plan->value)->toBe('pro')
        ->and($tenant->status)->toBe('active')
        ->and($tenant->vat_number)->toBe('BE0987654321')
        ->and($tenant->iban)->toBe('BE68539007547034');

    // 2. User & Pivot
    expect($user->email)->toBe('luc@vermeulentransport.be')
        ->and($tenant->users)->toHaveCount(1)
        ->and($tenant->users->first()->pivot->role)->toBe('owner');

    // 3. Belgian Tax Rates (4 rates)
    $taxRates = TaxRate::where('tenant_id', $tenant->id)->get();
    expect($taxRates)->toHaveCount(4);
    expect($taxRates->where('code', 'BE_21')->first()->rate_percentage)->toBe('21.00')
        ->and($taxRates->where('code', 'BE_0_MC')->first()->peppol_category_code)->toBe('AE');

    // 4. Default Categories
    $categories = Category::where('tenant_id', $tenant->id)->get();
    expect($categories)->toHaveCount(2)
        ->and($categories->pluck('slug')->all())->toContain('algemeen', 'nieuws-kennisbank');

    // 5. Homepage with blocks
    expect($homepage->is_homepage)->toBeTrue()
        ->and($homepage->blocks)->toBeArray()
        ->and(count($homepage->blocks))->toBeGreaterThan(2);

    // 6. Audit Log entry
    $audit = AuditLog::where('tenant_id', $tenant->id)->where('action', 'tenant.provisioned')->first();
    expect($audit)->not->toBeNull()
        ->and($audit->new_values['niche'])->toBe(TemplateBlocks::NICHE_TRANSPORT);
});

test('BuildPageBlocksAction updates blocks and sanitizes XSS injection', function () {
    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Demo Bedrijf',
        'slug' => 'demo-bedrijf',
        'plan' => 'start',
    ]);

    $page = Page::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'title' => 'Home',
        'slug' => 'home',
        'is_homepage' => true,
        'blocks' => [],
    ]);

    $action = app(BuildPageBlocksAction::class);

    $unsafeBlocks = [
        [
            'id' => 'block_1',
            'type' => 'hero',
            'data' => [
                'title' => 'Clean Title <script>alert("hacked")</script>',
                'subtitle' => 'Safe subtitle with <b>bold</b> text',
            ],
        ],
    ];

    $updatedPage = $action->execute($page, $unsafeBlocks);

    expect($updatedPage->blocks)->toHaveCount(1);
    expect($updatedPage->blocks[0]['data']['title'])->not->toContain('<script>')
        ->and($updatedPage->blocks[0]['data']['title'])->toBe('Clean Title alert("hacked")')
        ->and($updatedPage->blocks[0]['data']['subtitle'])->toContain('<b>bold</b>');

    $audit = AuditLog::where('tenant_id', $tenant->id)->where('action', 'page.blocks_updated')->first();
    expect($audit)->not->toBeNull();
});

test('SendCommercialDocumentAction dispatches email with payment link and logs communication', function () {
    Mail::fake();

    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Ziemkiewicz Digital Agency',
        'slug' => 'ziemkiewicz-digital',
        'plan' => 'scale',
    ]);

    $customer = Customer::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'company_name' => 'Janssens Logistics BV',
        'contact_person' => 'Jan Janssens',
        'email' => 'jan@janssens-logistics.be',
    ]);

    $invoice = Invoice::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-2026-0001',
        'total_amount' => 1500.00,
        'status' => 'draft',
    ]);

    $action = app(SendCommercialDocumentAction::class);
    $sentInvoice = $action->execute($invoice);

    expect($sentInvoice->status->value)->toBe('sent')
        ->and($sentInvoice->payment_token)->not->toBeEmpty();

    Mail::assertQueued(SendCommercialDocumentMail::class, function ($mail) use ($customer, $invoice) {
        return $mail->hasTo($customer->email)
            && str_contains($mail->envelope()->subject, $invoice->invoice_number);
    });

    $communication = Communication::where('tenant_id', $tenant->id)
        ->where('related_id', $invoice->id)
        ->first();

    expect($communication)->not->toBeNull()
        ->and($communication->type->value)->toBe('email')
        ->and($communication->body)->toContain($invoice->invoice_number);
});

test('AgentToolsSchema exposes valid tools and executes tool calls', function () {
    $declarations = AgentToolsSchema::getToolDeclarations();

    expect($declarations)->toBeArray()
        ->and(count($declarations))->toBe(4);

    $toolNames = array_column($declarations, 'name');
    expect($toolNames)->toContain(
        'tool_provision_tenant',
        'tool_build_page_blocks',
        'tool_create_catalog_item',
        'tool_send_invoice_payment_link'
    );

    $agentService = app(AgentToolsSchema::class);

    // Test tool_provision_tenant execution via agent
    $result = $agentService->handleToolCall('tool_provision_tenant', [
        'company_name' => 'AI Provisioned BV',
        'niche' => TemplateBlocks::NICHE_BREEDING,
        'plan' => 'start',
        'admin_name' => 'Katrien Peeters',
        'admin_email' => 'katrien@peeters-cattery.be',
    ]);

    expect($result['status'])->toBe('success')
        ->and($result['tenant_slug'])->toBe('ai-provisioned-bv')
        ->and($result['owner_email'])->toBe('katrien@peeters-cattery.be');
});
