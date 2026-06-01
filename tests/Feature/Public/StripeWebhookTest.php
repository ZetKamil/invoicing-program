<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provisions a new tenant and user upon successful stripe checkout session', function () {
    // Scenario: Simulate an inbound, valid checkout.session.completed Stripe webhook
    $registrationId = (string) Str::uuid();

    // Cache the registration payload simulating the /register flow
    Cache::put("registration_{$registrationId}", [
        'company_name' => 'Acme Logistics',
        'email' => 'admin@acmelogistics.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'plan' => 'pro',
    ], now()->addMinutes(10));

    // Force dummy secret for testing
    config(['services.stripe.webhook_secret' => 'whsec_dummy']);

    // Construct the simulated Webhook JSON payload
    $payload = [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'mode' => 'subscription',
                'customer' => 'cus_test123',
                'subscription' => 'sub_test123',
                'metadata' => [
                    'registration_id' => $registrationId,
                ],
            ]
        ]
    ];

    // Fire the webhook (bypassing signature validation because we use 'whsec_dummy' in testing)
    $response = $this->postJson('/webhook/stripe', $payload);

    // Assert successful receipt
    $response->assertStatus(200);

    // Assertion: Assert that the Tenant was created
    $this->assertDatabaseHas('tenants', [
        'name' => 'Acme Logistics',
        'stripe_subscription_id' => 'sub_test123',
        'stripe_subscription_status' => 'active',
    ]);

    // Retrieve the tenant to check the User
    $tenant = Tenant::where('name', 'Acme Logistics')->first();
    expect($tenant)->not->toBeNull();

    // Assertion: Assert that the Admin User was created
    $this->assertDatabaseHas('users', [
        'tenant_id' => $tenant->id,
        'email' => 'admin@acmelogistics.com',
        'name' => 'Admin',
    ]);

    // Assertion: Assert that the cache is successfully cleared
    expect(Cache::has("registration_{$registrationId}"))->toBeFalse();
});
