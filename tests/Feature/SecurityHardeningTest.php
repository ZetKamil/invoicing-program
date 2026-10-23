<?php

use App\Models\Bedrijf;
use Illuminate\Support\Facades\Config;

test('http responses include enterprise security headers', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

test('public user registration route is disabled', function () {
    // Fortify registration should return 404 because Features::registration() is disabled
    $response = $this->get('/register');
    $response->assertNotFound();

    $postResponse = $this->post('/register', [
        'name' => 'Attacker User',
        'email' => 'attacker@test.com',
        'password' => 'password12345!',
        'password_confirmation' => 'password12345!',
    ]);
    $postResponse->assertNotFound();
});

test('stripe webhook rejects unconfigured secret in production environment', function () {
    // Simulate production environment
    app()->detectEnvironment(fn () => 'production');
    Config::set('services.stripe.webhook_secret', 'whsec_dummy');

    $response = $this->postJson('/webhook/stripe', [
        'type' => 'invoice.paid',
    ]);

    $response->assertStatus(500);
    $response->assertJson(['error' => 'Webhook configuration error']);
});
