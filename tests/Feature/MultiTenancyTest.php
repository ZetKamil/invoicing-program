<?php

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('users can be assigned to a tenant and a role', function () {
    $tenant = Tenant::create([
        'name' => 'Test Logistics',
        'slug' => 'test-logistics',
    ]);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    expect($user->tenant_id)->toBe($tenant->id)
        ->and($user->role)->toBe(UserRole::ADMIN);
});

test('models with HasTenant trait are automatically scoped', function () {
    $tenant1 = Tenant::create(['name' => 'Tenant 1', 'slug' => 't1']);
    $tenant2 = Tenant::create(['name' => 'Tenant 2', 'slug' => 't2']);

    $user1 = User::create([
        'tenant_id' => $tenant1->id,
        'name' => 'User 1',
        'email' => 'u1@example.com',
        'password' => bcrypt('password'),
    ]);

    $user2 = User::create([
        'tenant_id' => $tenant2->id,
        'name' => 'User 2',
        'email' => 'u2@example.com',
        'password' => bcrypt('password'),
    ]);

    // Acting as User 1
    Auth::login($user1);
    
    // Should only see User 1
    expect(User::count())->toBe(1)
        ->and(User::first()->id)->toBe($user1->id);

    // Acting as User 2
    Auth::login($user2);
    
    // Should only see User 2
    expect(User::count())->toBe(1)
        ->and(User::first()->id)->toBe($user2->id);
});

test('tenant settings are cast to array', function () {
    $tenant = Tenant::create([
        'name' => 'Settings Tenant',
        'slug' => 'settings-tenant',
        'settings' => ['theme' => 'dark', 'notifications' => true],
    ]);

    $freshTenant = Tenant::find($tenant->id);
    
    expect($freshTenant->settings)->toBeArray()
        ->and($freshTenant->settings['theme'])->toBe('dark');
});
