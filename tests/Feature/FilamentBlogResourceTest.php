<?php

use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Enums\TenantPlan;
use App\Enums\UserRole;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('post model stores structured sections JSON and belongs to a blog category', function () {
    $tenant = Tenant::create([
        'name' => 'Katten Blog B.V.',
        'slug' => 'katten-blog',
        'plan' => TenantPlan::START,
    ]);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Auteur Jan',
        'email' => 'jan.auteur@example.com',
        'password' => bcrypt('secret'),
        'role' => UserRole::ADMIN,
    ]);

    $category = Category::create([
        'tenant_id' => $tenant->id,
        'type'       => CategoryType::POST,
        'name'       => 'Verzorging & Gezondheid',
        'slug'       => 'verzorging-en-gezondheid',
    ]);

    $post = Post::create([
        'tenant_id'    => $tenant->id,
        'user_id'      => $user->id,
        'category_id'  => $category->id,
        'title'        => 'Gids voor de verzorging van kittens',
        'slug'         => 'gids-voor-de-verzorging-van-kittens',
        'excerpt'      => 'Korte inleiding over kittenverzorging.',
        'status'       => PostStatus::PUBLISHED,
        'published_at' => now(),
        'sections'     => [
            [
                'heading' => 'Waarom kwalitatieve voeding essentieel is',
                'level'   => 'h2',
                'content' => 'Hoogwaardige voeding zorgt voor een gezonde vacht en sterke botten.',
            ],
            [
                'heading' => 'Eerste dierenartsbezoek en vaccinaties',
                'level'   => 'h2',
                'content' => 'Plan tijdig het eerste consult in bij de lokale dierenarts.',
            ],
        ],
    ]);

    expect($post->category->id)->toBe($category->id)
        ->and($post->category->type)->toBe(CategoryType::POST)
        ->and(count($post->sections))->toBe(2)
        ->and($post->sections[0]['heading'])->toBe('Waarom kwalitatieve voeding essentieel is')
        ->and($post->status)->toBe(PostStatus::PUBLISHED)
        ->and($post->status->label())->toBe('Gepubliceerd')
        ->and($post->status->badgeColor())->toBe('success');
});

test('authenticated user can view posts index and create page in filament', function () {
    $tenant = Tenant::create([
        'name'                       => 'Test Bedrijf Blog',
        'slug'                       => 'test-bedrijf-blog',
        'plan'                       => TenantPlan::START,
        'status'                     => 'active',
    ]);

    $user = User::create([
        'tenant_id'  => $tenant->id,
        'name'       => 'Beheerder',
        'email'      => 'beheerder@example.com',
        'password'   => bcrypt('password'),
        'role'       => UserRole::ADMIN,
    ]);

    $category = Category::create([
        'tenant_id' => $tenant->id,
        'type'       => CategoryType::POST,
        'name'       => 'Tips & Tricks',
        'slug'       => 'tips-and-tricks',
    ]);

    $post = Post::create([
        'tenant_id'    => $tenant->id,
        'user_id'      => $user->id,
        'category_id'  => $category->id,
        'title'        => 'Introductie tot onze cattery',
        'slug'         => 'introductie-tot-onze-cattery',
        'status'       => PostStatus::PUBLISHED,
        'published_at' => now(),
        'sections'     => [
            [
                'heading' => 'Onze filosofie',
                'level'   => 'h2',
                'content' => 'Welzijn en gezondheid van de dieren staan voorop.',
            ],
        ],
    ]);

    $this->actingAs($user);

    $indexResponse = $this->get(PostResource::getUrl('index'));
    $indexResponse->assertOk();
    $indexResponse->assertSee('Introductie tot onze cattery');
    $indexResponse->assertSee('Tips & Tricks');
    $indexResponse->assertSee('1 secties');

    $createResponse = $this->get(PostResource::getUrl('create'));
    $createResponse->assertOk();
});
