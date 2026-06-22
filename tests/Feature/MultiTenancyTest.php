<?php

use App\Enums\UserRole;
use App\Models\Bedrijf;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('users can be assigned to a bedrijf and a role', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Test Logistics',
        'slug' => 'test-logistics',
    ]);

    $user = User::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    expect($user->bedrijf_id)->toBe($bedrijf->id)
        ->and($user->role)->toBe(UserRole::ADMIN);
});

test('models with HasBedrijf trait are automatically scoped via BedrijfScope', function () {
    $bedrijf1 = Bedrijf::create(['name' => 'Bedrijf 1', 'slug' => 'b1']);
    $bedrijf2 = Bedrijf::create(['name' => 'Bedrijf 2', 'slug' => 'b2']);

    $user1 = User::create([
        'bedrijf_id' => $bedrijf1->id,
        'name' => 'User 1',
        'email' => 'u1@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    $user2 = User::create([
        'bedrijf_id' => $bedrijf2->id,
        'name' => 'User 2',
        'email' => 'u2@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    Customer::create(['bedrijf_id' => $bedrijf1->id, 'company_name' => 'Customer B1']);
    Customer::create(['bedrijf_id' => $bedrijf2->id, 'company_name' => 'Customer B2']);

    // Acting as User 1
    Auth::login($user1);
    
    // Should only see Customer B1 due to BedrijfScope
    expect(Customer::count())->toBe(1)
        ->and(Customer::first()->company_name)->toBe('Customer B1');

    // Acting as User 2
    Auth::login($user2);
    
    // Should only see Customer B2 due to BedrijfScope
    expect(Customer::count())->toBe(1)
        ->and(Customer::first()->company_name)->toBe('Customer B2');
});
