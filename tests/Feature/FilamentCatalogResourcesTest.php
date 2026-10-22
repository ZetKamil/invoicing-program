<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CategoryType;
use App\Enums\TenantPlan;
use App\Enums\UserRole;
use App\Filament\Resources\CatalogItems\CatalogItemResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Bedrijf;
use App\Models\CatalogItem;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view categories index page in filament', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Koci Dom',
        'slug' => 'koci-dom',
        'plan' => TenantPlan::START,
        'stripe_subscription_status' => 'active',
    ]);

    $user = User::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Admin Koci',
        'email' => 'koci@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    $category = Category::create([
        'bedrijf_id' => $bedrijf->id,
        'type' => CategoryType::CATALOG,
        'name' => 'Koty Hodowlane',
        'slug' => 'koty-hodowlane',
    ]);

    $this->actingAs($user);

    $response = $this->get(CategoryResource::getUrl('index'));
    $response->assertOk();
    $response->assertSee('Koty Hodowlane');
});

test('authenticated user can view catalog items index page in filament', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Hodowla Kotów',
        'slug' => 'hodowla-kotow',
        'plan' => TenantPlan::START,
        'stripe_subscription_status' => 'active',
    ]);

    $user = User::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Hodowca Jan',
        'email' => 'jan@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    $item = CatalogItem::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Kocur Apollo',
        'slug' => 'kocur-apollo',
        'status' => CatalogItemStatus::AVAILABLE,
        'price_minor' => 450000,
        'attributes' => [
            'breed' => 'British Shorthair',
            'color' => 'Niebieski',
        ],
    ]);

    $this->actingAs($user);

    $response = $this->get(CatalogItemResource::getUrl('index'));
    $response->assertOk();
    $response->assertSee('Kocur Apollo');
    $response->assertSee('4 500,00');
});

test('authenticated user can access create pages for category and catalog item', function () {
    $bedrijf = Bedrijf::create([
        'name' => 'Firma Testowa',
        'slug' => 'firma-testowa',
        'plan' => TenantPlan::START,
        'stripe_subscription_status' => 'active',
    ]);

    $user = User::create([
        'bedrijf_id' => $bedrijf->id,
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    $this->get(CategoryResource::getUrl('create'))->assertOk();
    $this->get(CatalogItemResource::getUrl('create'))->assertOk();
});
