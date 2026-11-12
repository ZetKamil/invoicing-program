<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CategoryType;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\CatalogItem;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

test('category and catalog_item use ULIDs and cast enums and JSON correctly', function () {
    $tenant = Tenant::create([
        'name' => 'Koty z Mazowieckiej Szwajcarii',
        'slug' => 'koty-mazowsze',
    ]);

    $category = Category::create([
        'tenant_id' => $tenant->id,
        'type' => CategoryType::CATALOG,
        'name' => 'Koty Hodowlane',
        'slug' => 'koty-hodowlane',
    ]);

    expect(strlen($category->id))->toBe(26) // ULID length
        ->and($category->type)->toBe(CategoryType::CATALOG)
        ->and($category->tenant_id)->toBe($tenant->id);

    $item = CatalogItem::create([
        'tenant_id' => $tenant->id,
        'category_id' => $category->id,
        'name' => 'Apollo',
        'slug' => 'apollo',
        'status' => CatalogItemStatus::AVAILABLE,
        'price_minor' => 350000, // 3 500,00 PLN w groszach
        'description' => 'Kocur brytyjski krótkowłosy',
        'attributes' => [
            'breed' => 'British Shorthair',
            'color' => 'blue',
            'gender' => 'male',
        ],
        'whatsapp_prefill_text' => 'Dzień dobry, pytam o kocura Apollo',
    ]);

    expect(strlen($item->id))->toBe(26)
        ->and($item->status)->toBe(CatalogItemStatus::AVAILABLE)
        ->and($item->price_minor)->toBe(350000)
        ->and($item->price_formatted)->toBe('3 500,00')
        ->and($item->attributes['breed'])->toBe('British Shorthair')
        ->and($item->category->id)->toBe($category->id);
});

test('categories and catalog_items are scoped to the authenticated tenant', function () {
    $tenant1 = Tenant::create(['name' => 'Tenant 1', 'slug' => 't1']);
    $tenant2 = Tenant::create(['name' => 'Tenant 2', 'slug' => 't2']);

    $user1 = User::create([
        'tenant_id' => $tenant1->id,
        'name' => 'Tenant 1 User',
        'email' => 't1@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
    ]);

    $user2 = User::create([
        'tenant_id' => $tenant2->id,
        'name' => 'Tenant 2 User',
        'email' => 't2@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
    ]);

    Category::create([
        'tenant_id' => $tenant1->id,
        'type' => CategoryType::CATALOG,
        'name' => 'Koty B1',
        'slug' => 'koty-b1',
    ]);

    Category::create([
        'tenant_id' => $tenant2->id,
        'type' => CategoryType::CATALOG,
        'name' => 'Psy B2',
        'slug' => 'psy-b2',
    ]);

    CatalogItem::create([
        'tenant_id' => $tenant1->id,
        'name' => 'Kot 1',
        'slug' => 'kot-1',
        'status' => CatalogItemStatus::AVAILABLE,
    ]);

    CatalogItem::create([
        'tenant_id' => $tenant2->id,
        'name' => 'Pies 2',
        'slug' => 'pies-2',
        'status' => CatalogItemStatus::AVAILABLE,
    ]);

    Auth::login($user1);
    expect(Category::count())->toBe(1)
        ->and(Category::first()->name)->toBe('Koty B1')
        ->and(CatalogItem::count())->toBe(1)
        ->and(CatalogItem::first()->name)->toBe('Kot 1');

    Auth::login($user2);
    expect(Category::count())->toBe(1)
        ->and(Category::first()->name)->toBe('Psy B2')
        ->and(CatalogItem::count())->toBe(1)
        ->and(CatalogItem::first()->name)->toBe('Pies 2');
});
