<?php

namespace App\Actions\Blog;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Services\GeminiService;
use Illuminate\Support\Str;

class GenerateBlogPostWithAiAction
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    /**
     * Generate and persist a structured blog post draft using AI.
     *
     * @param string $topic Target subject or keyword.
     * @param string|null $categoryId Optional blog category ID.
     * @param string $tone Tone of voice ('professioneel', 'toegankelijk', 'expert').
     * @param string|null $bedrijfId Optional tenant ID.
     * @param string|null $userId Optional author ID.
     * @return Post The newly created draft post.
     */
    public function handle(
        string $topic,
        ?string $categoryId = null,
        string $tone = 'professioneel',
        ?string $bedrijfId = null,
        ?string $userId = null
    ): Post {
        $categoryName = null;
        if ($categoryId) {
            $categoryName = Category::find($categoryId)?->name;
        }

        $data = $this->geminiService->generateStructuredArticle($topic, $tone, $categoryName);

        $baseSlug = Str::slug($data['slug'] ?? $data['title'] ?? $topic);
        $slug = $baseSlug;
        $counter = 1;

        // Ensure unique slug within tenant
        $targetBedrijfId = $bedrijfId ?? auth()->user()?->bedrijf_id;
        while (Post::withoutGlobalScopes()->where('bedrijf_id', $targetBedrijfId)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return Post::create([
            'bedrijf_id'   => $targetBedrijfId,
            'user_id'      => $userId ?? auth()->id(),
            'category_id'  => $categoryId,
            'title'        => $data['title'],
            'slug'         => $slug,
            'excerpt'      => $data['excerpt'],
            'status'       => PostStatus::DRAFT,
            'sections'     => $data['sections'] ?? [],
        ]);
    }
}
