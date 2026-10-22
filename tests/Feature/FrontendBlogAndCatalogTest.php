<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Enums\TenantPlan;
use App\Enums\UserRole;
use App\Models\Bedrijf;
use App\Models\CatalogItem;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->bedrijf = Bedrijf::create([
        'name' => 'Katten Cattery B.V.',
        'slug' => 'katten-cattery',
        'plan' => TenantPlan::START,
    ]);

    $this->user = User::create([
        'bedrijf_id' => $this->bedrijf->id,
        'name'       => 'Beheerder Sophie',
        'email'      => 'sophie@example.com',
        'password'   => bcrypt('secret'),
        'role'       => UserRole::ADMIN,
    ]);
});

test('guest can view published blog posts and filter by category on blog index', function () {
    $category = Category::create([
        'bedrijf_id' => $this->bedrijf->id,
        'type'       => CategoryType::POST,
        'name'       => 'Kattenverzorging',
        'slug'       => 'kattenverzorging',
    ]);

    $publishedPost = Post::create([
        'bedrijf_id'   => $this->bedrijf->id,
        'user_id'      => $this->user->id,
        'category_id'  => $category->id,
        'title'        => 'Gids voor voeding van kittens',
        'slug'         => 'gids-voor-voeding-van-kittens',
        'excerpt'      => 'Alles wat u moet weten over gezonde voeding.',
        'status'       => PostStatus::PUBLISHED,
        'published_at' => now(),
        'sections'     => [
            [
                'heading' => 'Hoogwaardige eiwitten',
                'level'   => 'h2',
                'content' => 'Kittens hebben veel dierlijke eiwitten nodig voor de spierontwikkeling.',
            ],
        ],
    ]);

    $draftPost = Post::create([
        'bedrijf_id'   => $this->bedrijf->id,
        'user_id'      => $this->user->id,
        'category_id'  => $category->id,
        'title'        => 'Geheim concept artikel',
        'slug'         => 'geheim-concept-artikel',
        'status'       => PostStatus::DRAFT,
    ]);

    $response = $this->get(route('blog'));
    $response->assertOk();
    $response->assertSee('Gids voor voeding van kittens');
    $response->assertSee('Kattenverzorging');
    $response->assertDontSee('Geheim concept artikel');

    // Filter by category
    $filteredResponse = $this->get(route('blog', ['categorie' => 'kattenverzorging']));
    $filteredResponse->assertOk();
    $filteredResponse->assertSee('Gids voor voeding van kittens');
});

test('guest can view single blog post with structured sections, custom headings, and media', function () {
    $category = Category::create([
        'bedrijf_id' => $this->bedrijf->id,
        'type'       => CategoryType::POST,
        'name'       => 'Rasstandaard',
        'slug'       => 'rasstandaard',
    ]);

    $post = Post::create([
        'bedrijf_id'   => $this->bedrijf->id,
        'user_id'      => $this->user->id,
        'category_id'  => $category->id,
        'title'        => 'Karaktereigenschappen van de Bengaal',
        'slug'         => 'karaktereigenschappen-van-de-bengaal',
        'excerpt'      => 'Actief, speels en intelligent.',
        'status'       => PostStatus::PUBLISHED,
        'published_at' => now(),
        'sections'     => [
            [
                'heading'       => 'Energiek en nieuwsgierig',
                'level'         => 'h2',
                'content'       => 'Bengalen houden van klimmen en interactief spelen.',
                'image_url'     => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba',
                'image_caption' => 'Spelende Bengaalse kat in actie',
            ],
            [
                'heading' => 'Sociale interactie',
                'level'   => 'h3',
                'content' => 'Ze hechten zich sterk aan hun adoptanten.',
            ],
        ],
    ]);

    $response = $this->get(route('blog.show', $post->slug));
    $response->assertOk();
    $response->assertSee('Karaktereigenschappen van de Bengaal');
    $response->assertSee('Energiek en nieuwsgierig');
    $response->assertSee('text-h2');
    $response->assertSee('Sociale interactie');
    $response->assertSee('text-h3');
    $response->assertSee('Spelende Bengaalse kat in actie');
});

test('guest receives 404 for unpublished draft post', function () {
    $draftPost = Post::create([
        'bedrijf_id' => $this->bedrijf->id,
        'user_id'    => $this->user->id,
        'title'      => 'Nog niet klaar',
        'slug'       => 'nog-niet-klaar',
        'status'     => PostStatus::DRAFT,
    ]);

    $response = $this->get(route('blog.show', $draftPost->slug));
    $response->assertNotFound();
});

test('guest can view catalog index with minor unit prices and dynamic attributes', function () {
    $category = Category::create([
        'bedrijf_id' => $this->bedrijf->id,
        'type'       => CategoryType::CATALOG,
        'name'       => 'Beschikbare Kittens',
        'slug'       => 'beschikbare-kittens',
    ]);

    $availableItem = CatalogItem::create([
        'bedrijf_id'   => $this->bedrijf->id,
        'category_id'  => $category->id,
        'name'         => 'Bengaal Kitten Mila',
        'slug'         => 'bengaal-kitten-mila',
        'status'       => CatalogItemStatus::AVAILABLE,
        'price_minor'  => 350000, // € 3.500,00
        'description'  => 'Prachtige rozetten, zachtaardig karakter.',
        'attributes'   => [
            'ras'      => 'Bengaal',
            'geslacht' => 'Poes',
            'kleur'    => 'Brown Spotted Tabby',
        ],
    ]);

    $hiddenItem = CatalogItem::create([
        'bedrijf_id'  => $this->bedrijf->id,
        'category_id' => $category->id,
        'name'        => 'Verborgen Item',
        'slug'        => 'verborgen-item',
        'status'      => CatalogItemStatus::HIDDEN,
        'price_minor' => 100000,
    ]);

    $response = $this->get(route('catalog.index'));
    $response->assertOk();
    $response->assertSee('Bengaal Kitten Mila');
    $response->assertSee('Beschikbaar');
    $response->assertSee('€ 3 500,00');
    $response->assertSee('Bengaal');
    $response->assertDontSee('Verborgen Item');
});

test('guest can view catalog item detail page with WhatsApp direct inquiry action', function () {
    $category = Category::create([
        'bedrijf_id' => $this->bedrijf->id,
        'type'       => CategoryType::CATALOG,
        'name'       => 'Dekkater',
        'slug'       => 'dekkater',
    ]);

    $item = CatalogItem::create([
        'bedrijf_id'             => $this->bedrijf->id,
        'category_id'            => $category->id,
        'name'                   => 'Kampioen Thor',
        'slug'                   => 'kampioen-thor',
        'status'                 => CatalogItemStatus::AVAILABLE,
        'price_minor'            => 120000, // € 1.200,00
        'description'            => 'Gezonde dekkater met stamboom en HCM/PKD testen negatief.',
        'whatsapp_prefill_text'  => 'Hallo, ik wil graag een dekking reserveren voor Thor.',
        'attributes'             => [
            'ras'      => 'Bengaal',
            'bloedgroep' => 'A',
            'stamboom' => 'Fife & TICA',
        ],
    ]);

    $response = $this->get(route('catalog.show', $item->slug));
    $response->assertOk();
    $response->assertSee('Kampioen Thor');
    $response->assertSee('€ 1 200,00');
    $response->assertSee('Bloedgroep');
    $response->assertSee('Fife &amp; TICA', false);
    $response->assertSee('Direct via WhatsApp');
    $response->assertSee(rawurlencode('Hallo, ik wil graag een dekking reserveren voor Thor.'));
});
