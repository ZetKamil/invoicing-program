<?php

use App\Enums\TenantPlan;
use App\Enums\UserRole;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('tenant on start plan cannot access ERP resources (invoices, quotes, products)', function () {
    $tenant = Tenant::create([
        'name' => 'Koci Zakątek',
        'slug' => 'koci-zakatek',
        'plan' => TenantPlan::START,
    ]);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Hodowca',
        'email' => 'hodowca@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
        'is_super_admin' => false,
    ]);

    Auth::login($user);

    expect($tenant->hasErpAccess())->toBeFalse()
        ->and(InvoiceResource::canViewAny())->toBeFalse()
        ->and(QuoteResource::canViewAny())->toBeFalse()
        ->and(ProductResource::canViewAny())->toBeFalse();
});

test('tenant on pro plan can access ERP resources', function () {
    $tenant = Tenant::create([
        'name' => 'Pro Transport Sp. z o.o.',
        'slug' => 'pro-transport',
        'plan' => TenantPlan::PRO,
    ]);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Kierownik Logistyki',
        'email' => 'logistyka@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
        'is_super_admin' => false,
    ]);

    Auth::login($user);

    expect($tenant->hasErpAccess())->toBeTrue()
        ->and(InvoiceResource::canViewAny())->toBeTrue()
        ->and(QuoteResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canViewAny())->toBeTrue();
});

test('tenant on scale plan can access ERP resources', function () {
    $tenant = Tenant::create([
        'name' => 'Mega Logistics International',
        'slug' => 'mega-logistics',
        'plan' => TenantPlan::SCALE,
    ]);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Dyrektor',
        'email' => 'dyrektor@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
        'is_super_admin' => false,
    ]);

    Auth::login($user);

    expect($tenant->hasErpAccess())->toBeTrue()
        ->and(InvoiceResource::canViewAny())->toBeTrue()
        ->and(QuoteResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canViewAny())->toBeTrue();
});

test('super admin always has access to ERP resources regardless of tenant plan', function () {
    $tenant = Tenant::create([
        'name' => 'Dowolna Firma',
        'slug' => 'dowolna-firma',
        'plan' => TenantPlan::START,
    ]);

    $superAdmin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
        'is_super_admin' => true,
    ]);

    Auth::login($superAdmin);

    expect(InvoiceResource::canViewAny())->toBeTrue()
        ->and(QuoteResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canViewAny())->toBeTrue();
});
