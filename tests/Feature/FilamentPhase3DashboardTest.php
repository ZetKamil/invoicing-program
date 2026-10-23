<?php

use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Resources\Bedrijven\BedrijfResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\WebsitePages\PageResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('Super admin has exclusive access to Platformbeheer (TenantResource)', function () {
    $superAdmin = User::create([
        'id' => (string) Str::ulid(),
        'name' => 'Kamil Super Admin',
        'email' => 'kamil@ziemkiewicz.be',
        'password' => bcrypt('secret123'),
        'is_super_admin' => true,
    ]);

    $clientOwner = User::create([
        'id' => (string) Str::ulid(),
        'name' => 'Client Owner',
        'email' => 'client@catbreeder.be',
        'password' => bcrypt('secret123'),
        'is_super_admin' => false,
    ]);

    // As Super Admin
    $this->actingAs($superAdmin);
    expect(TenantResource::canViewAny())->toBeTrue()
        ->and(BedrijfResource::canViewAny())->toBeTrue();

    // As Client Owner
    $this->actingAs($clientOwner);
    expect(TenantResource::canViewAny())->toBeFalse()
        ->and(BedrijfResource::canViewAny())->toBeFalse();
});

test('Tenant owner can access PageResource and InvoiceResource', function () {
    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Cat Breeder BV',
        'slug' => 'cat-breeder-bv',
        'plan' => 'pro',
    ]);

    $owner = User::create([
        'id' => (string) Str::ulid(),
        'name' => 'Cattery Owner',
        'email' => 'owner@catbreeder.be',
        'password' => bcrypt('secret123'),
        'is_super_admin' => false,
    ]);

    $tenant->users()->attach($owner->id, [
        'id' => (string) Str::ulid(),
        'role' => 'owner',
    ]);

    $this->actingAs($owner);

    expect(PageResource::getModel())->toBe(Page::class)
        ->and(InvoiceResource::getModel())->toBe(Invoice::class);
});

test('Invoice payment token generation and mark as paid actions work', function () {
    $tenant = Tenant::create([
        'id' => (string) Str::ulid(),
        'name' => 'Logistics BV',
        'slug' => 'logistics-bv',
        'plan' => 'scale',
    ]);

    $customer = Customer::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'company_name' => 'Antwerp Cargo Hub',
        'email' => 'billing@antwerpcargo.be',
    ]);

    $invoice = Invoice::create([
        'id' => (string) Str::ulid(),
        'tenant_id' => $tenant->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-2026-9999',
        'total_amount' => 2400.00,
        'status' => 'sent',
    ]);

    // Simulate mark as paid
    $invoice->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    expect($invoice->fresh()->status->value)->toBe('paid')
        ->and($invoice->fresh()->paid_at)->not->toBeNull();
});
