<?php

use App\Enums\TenantPlan;
use App\Enums\UserRole;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Models\Bedrijf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('tenant on start plan cannot access ERP resources (invoices, quotes, products)', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Koci Zakątek',
        'slug' => 'koci-zakatek',
        'plan' => TenantPlan::START,
    ]);

    $user = User::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Hodowca',
        'email' => 'hodowca@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
        'is_super_admin' => false,
    ]);

    Auth::login($user);

    expect($bedrijf->hasErpAccess())->toBeFalse()
        ->and(InvoiceResource::canViewAny())->toBeFalse()
        ->and(QuoteResource::canViewAny())->toBeFalse()
        ->and(ProductResource::canViewAny())->toBeFalse();
});

test('tenant on pro plan can access ERP resources', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Pro Transport Sp. z o.o.',
        'slug' => 'pro-transport',
        'plan' => TenantPlan::PRO,
    ]);

    $user = User::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Kierownik Logistyki',
        'email' => 'logistyka@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
        'is_super_admin' => false,
    ]);

    Auth::login($user);

    expect($bedrijf->hasErpAccess())->toBeTrue()
        ->and(InvoiceResource::canViewAny())->toBeTrue()
        ->and(QuoteResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canViewAny())->toBeTrue();
});

test('tenant on scale plan can access ERP resources', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Mega Logistics International',
        'slug' => 'mega-logistics',
        'plan' => TenantPlan::SCALE,
    ]);

    $user = User::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Dyrektor',
        'email' => 'dyrektor@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
        'is_super_admin' => false,
    ]);

    Auth::login($user);

    expect($bedrijf->hasErpAccess())->toBeTrue()
        ->and(InvoiceResource::canViewAny())->toBeTrue()
        ->and(QuoteResource::canViewAny())->toBeTrue()
        ->and(ProductResource::canViewAny())->toBeTrue();
});

test('super admin always has access to ERP resources regardless of tenant plan', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Dowolna Firma',
        'slug' => 'dowolna-firma',
        'plan' => TenantPlan::START,
    ]);

    $superAdmin = User::create([
        'bedrijf_id' => $bedrijf->id,
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
