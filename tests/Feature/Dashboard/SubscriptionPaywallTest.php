<?php

use App\Models\Tenant;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects user to billing inactive if subscription is not active', function () {
    // Scenario: Create a Tenant with stripe_subscription_status => 'unpaid' or 'cancelled'
    $tenant = Tenant::factory()->create([
        'name' => 'Lapsed Tenant',
        'stripe_subscription_status' => 'canceled',
    ]);

    // Authenticate their Admin user
    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    // Assertion: Attempt to access dashboard
    $response = $this->get('/dashboard');

    // The middleware CheckSubscriptionStatus must intercept and redirect to /billing/inactive
    $response->assertRedirect(route('billing.inactive'));
});

it('allows access to dashboard if subscription is active', function () {
    $tenant = Tenant::factory()->create([
        'name' => 'Active Tenant',
        'stripe_subscription_status' => 'active',
    ]);

    $user = User::factory()->create([
        'tenant_id' => $tenant->id,
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200);
});
