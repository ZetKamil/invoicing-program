<?php

use App\Actions\Blog\GenerateBlogPostWithAiAction;
use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Enums\TenantPlan;
use App\Enums\UserRole;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Bedrijf;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->bedrijf = Bedrijf::create([
        'name' => 'Katten & Transport B.V.',
        'slug' => 'katten-transport',
        'plan' => TenantPlan::START,
    ]);

    $this->user = User::create([
        'bedrijf_id' => $this->bedrijf->id,
        'name'       => 'Admin Tester',
        'email'      => 'admin.tester@example.com',
        'password'   => bcrypt('password'),
        'role'       => UserRole::ADMIN,
    ]);
});

test('gemini service throws domain exception when api key is not set', function () {
    Config::set('services.gemini.api_key', null);

    $service = new GeminiService();

    expect(fn () => $service->generateStructuredArticle('Voeding van kittens'))
        ->toThrow(DomainException::class, 'Google Gemini API-sleutel ontbreekt');
});

test('gemini service parses structured json response correctly', function () {
    Config::set('services.gemini.api_key', 'test-fake-gemini-key');
    Config::set('services.gemini.model', 'gemini-1.5-flash');

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'title'    => 'Complete Voedingsgids voor Katten',
                                    'slug'     => 'complete-voedingsgids-voor-katten',
                                    'excerpt'  => 'Een diepgaand overzicht van verantwoorde kattenvoeding.',
                                    'sections' => [
                                        [
                                            'heading'       => 'Dierlijke eiwitten als basis',
                                            'level'         => 'h2',
                                            'content'       => 'Katten zijn obligate carnivoren.',
                                            'image_caption' => 'Kwalitatieve voeding voor katten',
                                        ],
                                        [
                                            'heading' => 'Het belang van hydratatie',
                                            'level'   => 'h3',
                                            'content' => 'Natte voeding ondersteunt de nieren.',
                                        ],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $service = new GeminiService();
    $result = $service->generateStructuredArticle('Voeding van katten', 'professioneel');

    expect($result['title'])->toBe('Complete Voedingsgids voor Katten')
        ->and($result['slug'])->toBe('complete-voedingsgids-voor-katten')
        ->and(count($result['sections']))->toBe(2)
        ->and($result['sections'][0]['level'])->toBe('h2')
        ->and($result['sections'][1]['heading'])->toBe('Het belang van hydratatie');
});

test('generate blog post with ai action creates draft post with structured sections', function () {
    Config::set('services.gemini.api_key', 'test-fake-gemini-key');

    $category = Category::create([
        'bedrijf_id' => $this->bedrijf->id,
        'type'       => CategoryType::POST,
        'name'       => 'Verzorging',
        'slug'       => 'verzorging',
    ]);

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'title'    => 'Vaccinatieschema voor Kittens',
                                    'slug'     => 'vaccinatieschema-voor-kittens',
                                    'excerpt'  => 'Bescherm uw kitten tegen ernstige ziektes met een tijdig vaccinatieschema.',
                                    'sections' => [
                                        [
                                            'heading'       => 'Eerste inenting op 9 weken',
                                            'level'         => 'h2',
                                            'content'       => 'De eerste basisvaccinatie beschermt tegen niesziekte en kattenziekte.',
                                            'image_caption' => 'Dierenarts onderzoekt kitten',
                                        ],
                                        [
                                            'heading' => 'Herhalingsvaccin op 12 weken',
                                            'level'   => 'h2',
                                            'content' => 'De booster zorgt voor langdurige immuniteit.',
                                        ],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $action = app(GenerateBlogPostWithAiAction::class);

    $post = $action->handle(
        topic: 'Vaccinatieschema voor kittens',
        categoryId: $category->id,
        tone: 'expert',
        bedrijfId: $this->bedrijf->id,
        userId: $this->user->id,
    );

    expect($post)->toBeInstanceOf(Post::class)
        ->and($post->title)->toBe('Vaccinatieschema voor Kittens')
        ->and($post->slug)->toBe('vaccinatieschema-voor-kittens')
        ->and($post->status)->toBe(PostStatus::DRAFT)
        ->and($post->category_id)->toBe($category->id)
        ->and($post->user_id)->toBe($this->user->id)
        ->and($post->bedrijf_id)->toBe($this->bedrijf->id)
        ->and(count($post->sections))->toBe(2)
        ->and($post->sections[0]['heading'])->toBe('Eerste inenting op 9 weken');

    $this->assertDatabaseHas('posts', [
        'id'          => $post->id,
        'title'       => 'Vaccinatieschema voor Kittens',
        'status'      => 'draft',
        'category_id' => $category->id,
    ]);
});

test('filament posts list has generate with ai action that creates post and redirects', function () {
    Config::set('services.gemini.api_key', 'test-fake-gemini-key');

    $category = Category::create([
        'bedrijf_id' => $this->bedrijf->id,
        'type'       => CategoryType::POST,
        'name'       => 'Gezondheid',
        'slug'       => 'gezondheid',
    ]);

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'title'    => 'Kattenbak Training van Jonge Katten',
                                    'slug'     => 'kattenbak-training-van-jonge-katten',
                                    'excerpt'  => 'Tips voor zindelijkheid bij kittens.',
                                    'sections' => [
                                        [
                                            'heading' => 'Kies de juiste kattenbakvulling',
                                            'level'   => 'h2',
                                            'content' => 'Fijnkorrelige, ongeparfumeerde vulling heeft de voorkeur.',
                                        ],
                                    ],
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->actingAs($this->user);

    Livewire::test(ListPosts::class)
        ->callAction('generateAi', [
            'topic'       => 'Kattenbak training van jonge katten',
            'category_id' => $category->id,
            'tone'        => 'toegankelijk',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('posts', [
        'title'       => 'Kattenbak Training van Jonge Katten',
        'status'      => 'draft',
        'category_id' => $category->id,
    ]);
});
