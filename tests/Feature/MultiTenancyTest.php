<?php

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Customer;
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

test('models with HasTenant trait are automatically scoped via TenantScope', function () {
    $tenant1 = Tenant::create(['name' => 'Tenant 1', 'slug' => 't1']);
    $tenant2 = Tenant::create(['name' => 'Tenant 2', 'slug' => 't2']);

    $user1 = User::create([
        'tenant_id' => $tenant1->id,
        'name' => 'User 1',
        'email' => 'u1@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    $user2 = User::create([
        'tenant_id' => $tenant2->id,
        'name' => 'User 2',
        'email' => 'u2@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    Customer::create(['tenant_id' => $tenant1->id, 'company_name' => 'Customer T1']);
    Customer::create(['tenant_id' => $tenant2->id, 'company_name' => 'Customer T2']);

    // Acting as User 1
    Auth::login($user1);
    
    // Should only see Customer T1 due to TenantScope
    expect(Customer::count())->toBe(1)
        ->and(Customer::first()->company_name)->toBe('Customer T1');

    // Acting as User 2
    Auth::login($user2);
    
    // Should only see Customer T2 due to TenantScope
    expect(Customer::count())->toBe(1)
        ->and(Customer::first()->company_name)->toBe('Customer T2');
});
