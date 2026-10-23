<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Enums\PostStatus;
use App\Models\Bedrijf;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Display a listing of published blog posts.
     */
    public function index(Request $request): View
    {
        $bedrijf = Bedrijf::where('custom_domain', $request->getHost())->first() ?? Bedrijf::first();

        $query = Post::query()
            ->with(['category', 'user'])
            ->where('status', PostStatus::PUBLISHED)
            ->latest('published_at');

        if ($bedrijf) {
            $query->where('tenant_id', $bedrijf->id);
        }

        $activeCategory = null;
        if ($request->filled('categorie')) {
            $categorySlug = $request->query('categorie');
            $activeCategory = Category::where('slug', $categorySlug)
                ->where('type', CategoryType::POST)
                ->first();

            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        $posts = $query->paginate(9)->withQueryString();

        $categories = Category::query()
            ->where('type', CategoryType::POST)
            ->when($bedrijf, fn ($q) => $q->where('tenant_id', $bedrijf->id))
            ->whereHas('posts', fn ($q) => $q->where('status', PostStatus::PUBLISHED))
            ->withCount(['posts' => fn ($q) => $q->where('status', PostStatus::PUBLISHED)])
            ->get();

        return view('pages.blog.index', compact('posts', 'categories', 'activeCategory', 'bedrijf'));
    }

    /**
     * Display a single published blog post with its structured sections.
     */
    public function show(Request $request, string $slug): View
    {
        $post = Post::query()
            ->with(['category', 'user'])
            ->where('slug', $slug)
            ->where('status', PostStatus::PUBLISHED)
            ->firstOrFail();

        $relatedPosts = Post::query()
            ->where('tenant_id', $post->tenant_id)
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', PostStatus::PUBLISHED)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('pages.blog.show', compact('post', 'relatedPosts'));
    }
}
