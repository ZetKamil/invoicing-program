<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\Lead;
use App\Enums\UserRole;

it('blocks non-super-admins from accessing the TenantResource', function () {
    $tenant = Tenant::factory()->create(['stripe_subscription_status' => 'active']);
    $admin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::ADMIN,
        'is_super_admin' => false,
    ]);

    $this->actingAs($admin);
    \Filament\Facades\Filament::setTenant($tenant);

    $response = $this->get(\App\Filament\Resources\Tenants\TenantResource::getUrl('index'));

    $response->assertStatus(403);
});

it('allows super-admins to access the TenantResource', function () {
    $tenant = Tenant::factory()->create(['stripe_subscription_status' => 'active']);
    $superAdmin = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::ADMIN,
        'is_super_admin' => true,
    ]);

    $this->actingAs($superAdmin);
    \Filament\Facades\Filament::setTenant($tenant);

    $response = $this->get(\App\Filament\Resources\Tenants\TenantResource::getUrl('index'));

    $response->assertStatus(200);
});

it('scopes manually created leads correctly to the authenticated users tenant', function () {
    $tenantA = Tenant::factory()->create(['stripe_subscription_status' => 'active']);
    $userA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'role' => UserRole::DISPATCHER,
    ]);

    $tenantB = Tenant::factory()->create(['stripe_subscription_status' => 'active']);
    $userB = User::factory()->create([
        'tenant_id' => $tenantB->id,
        'role' => UserRole::DISPATCHER,
    ]);

    $this->actingAs($userA);
    \Filament\Facades\Filament::setTenant($tenantA);

    // Create lead manually inside Tenant A via Filament Action
    // Since we are mocking Livewire interactions, we can just hit the resource directly or test standard Eloquent scoping
    // We'll test Livewire CreateAction for LeadResource
    \Livewire\Livewire::test(\App\Filament\Resources\Leads\Pages\ListLeads::class)
        ->callAction('create', [
            'company_name' => 'Manual Lead Tenant A',
            'contact_person' => 'John Doe',
            'email' => 'john@test.com',
            'status' => \App\Enums\LeadStatus::NEW->value,
        ])
        ->assertHasNoActionErrors();

    // Assert the lead was created and belongs to Tenant A
    $this->assertDatabaseHas('leads', [
        'company_name' => 'Manual Lead Tenant A',
        'tenant_id' => $tenantA->id,
    ]);

    // Assert Tenant B cannot see this lead
    $this->actingAs($userB);
    \Filament\Facades\Filament::setTenant($tenantB);
    
    // In Filament, attempting to load an edit page or view for a record not in your scope throws 404 (ModelNotFoundException)
    $leadA = Lead::where('company_name', 'Manual Lead Tenant A')->first();
    
    $response = $this->get(\App\Filament\Resources\Leads\LeadResource::getUrl('index'));
    $response->assertStatus(200);
    $response->assertDontSee('Manual Lead Tenant A');
});

it('restricts access to invoice resource based on active_packages', function () {
    $tenantWithoutInvoicing = Tenant::factory()->create([
        'stripe_subscription_status' => 'active',
        'active_packages' => ['1', '2'], // Doesn't have package 3
    ]);
    
    $userWithoutInvoicing = User::factory()->create([
        'tenant_id' => $tenantWithoutInvoicing->id,
        'role' => UserRole::DISPATCHER,
    ]);

    $this->actingAs($userWithoutInvoicing);
    \Filament\Facades\Filament::setTenant($tenantWithoutInvoicing);

    $response = $this->get(\App\Filament\Resources\Invoices\InvoiceResource::getUrl('index'));
    $response->assertStatus(403);

    $tenantWithInvoicing = Tenant::factory()->create([
        'stripe_subscription_status' => 'active',
        'active_packages' => ['1', '3'], // Has package 3
    ]);
    
    $userWithInvoicing = User::factory()->create([
        'tenant_id' => $tenantWithInvoicing->id,
        'role' => UserRole::DISPATCHER,
    ]);

    $this->actingAs($userWithInvoicing);
    \Filament\Facades\Filament::setTenant($tenantWithInvoicing);

    $response = $this->get(\App\Filament\Resources\Invoices\InvoiceResource::getUrl('index'));
    $response->assertStatus(200);
});
