<?php

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents user a from viewing or editing tenant b invoices', function () {
    // Scenario: Create Tenant A and Tenant B.
    $tenantA = Tenant::factory()->create(['name' => 'Tenant A', 'stripe_subscription_status' => 'active']);
    $tenantB = Tenant::factory()->create(['name' => 'Tenant B', 'stripe_subscription_status' => 'active']);

    // Authenticate a User belonging to Tenant A.
    $userA = User::factory()->create([
        'tenant_id' => $tenantA->id,
        'role' => UserRole::ADMIN,
    ]);

    // Create an Invoice belonging to Tenant B.
    $invoiceB = Invoice::create([
        'tenant_id' => $tenantB->id,
        'customer_id' => 1, // Dummy ID
        'customer_type' => 'App\\Models\\Lead',
        'invoice_number' => 'INV-B-001',
        'issue_date' => now(),
        'due_date' => now()->addDays(14),
        'status' => InvoiceStatus::DRAFT,
        'subtotal' => 100.00,
        'tax_total' => 21.00,
        'total_amount' => 121.00,
    ]);

    $this->actingAs($userA);

    // Assertion: Attempt to access Tenant B's invoice edit page via Filament
    $response = $this->get('/dashboard/invoices/' . $invoiceB->id . '/edit');

    // The system MUST return a 403 or 404 status code
    $response->assertStatus(404); // Usually 404 because global scope hides it completely
});
