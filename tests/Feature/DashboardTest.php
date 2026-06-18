<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect(route('filament.dashboard.auth.login'));
});

test('authenticated users can visit the dashboard', function () {
    $bedrijf = \App\Models\Bedrijf::factory()->create(['stripe_subscription_status' => 'active']);
    $user = User::factory()->create([
        'role' => \App\Enums\UserRole::ADMIN,
        'bedrijf_id' => $bedrijf->id,
    ]);
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertOk();
});