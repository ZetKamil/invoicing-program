@extends('layouts.guest')
@section('title', 'Kennisbank & Blog | ' . ($bedrijf->name ?? 'TransDigit Master'))

@section('content')
<section id="blog-header" style="padding-top: 140px; padding-bottom: 40px; background: var(--navy-mid);">
    <div class="container">
        <p class="section-tag">Kennisbank & Inzichten</p>
        <h1 class="section-title">Artikelen, updates en expertise</h1>
        <p class="section-sub">Ontdek onze diepgaande gidsen, best practices en actuele ontwikkelingen.</p>

        @if($categories->isNotEmpty())
            <div class="category-pills" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 30px;">
                <a href="{{ route('blog') }}" class="pill-btn {{ is_null($activeCategory) ? 'active' : '' }}">
                    Alle categorieën
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('blog', ['categorie' => $category->slug]) }}" 
                       class="pill-btn {{ $activeCategory?->id === $category->id ? 'active' : '' }}">
                        {{ $category->name }} ({{ $category->posts_count }})
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section id="blog-content" style="padding: 60px 0 100px; background: var(--navy);">
    <div class="container">
        @if($posts->isEmpty())
            <div class="empty-state" style="text-align: center; padding: 80px 20px; background: var(--navy-card); border: 1px solid var(--border); border-radius: 16px;">
                <div style="font-size: 40px; margin-bottom: 16px;">📚</div>
                <h3 style="color: #fff; font-size: 20px; margin-bottom: 8px;">Geen artikelen gevonden</h3>
                <p style="color: var(--muted); font-size: 14px;">Er zijn momenteel geen gepubliceerde artikelen in deze selectie.</p>
                @if($activeCategory)
                    <a href="{{ route('blog') }}" class="btn-outline" style="margin-top: 20px; display: inline-flex;">
                        Alle artikelen bekijken
                    </a>
                @endif
            </div>
        @else
            <div class="blog-grid">
                @foreach($posts as $post)
                    <article class="blog-card" onclick="window.location.href='{{ route('blog.show', $post->slug) }}'">
                        <div class="blog-body">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                @if($post->category)
                                    <span class="blog-cat">{{ $post->category->name }}</span>
                                @else
                                    <span class="blog-cat">Algemeen</span>
                                @endif

                                @if(!empty($post->sections) && is_array($post->sections))
                                    <span style="font-size: 11px; color: var(--dim); background: rgba(255,255,255,0.05); padding: 2px 8px; border-radius: 4px;">
                                        {{ count($post->sections) }} secties
                                    </span>
                                @endif
                            </div>

                            <h2 class="blog-title">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>

                            @if($post->excerpt)
                                <p class="blog-excerpt">{{ Str::limit($post->excerpt, 120) }}</p>
                            @endif

                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border);">
                                <span style="font-size: 12px; color: var(--dim);">
                                    {{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}
                                </span>
                                <span class="blog-read">Lees verder →</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 48px;">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</section>

<style>
.category-pills .pill-btn {
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    transition: all 0.2s;
    text-decoration: none;
}
.category-pills .pill-btn:hover {
    color: #fff;
    border-color: var(--border-o);
}
.category-pills .pill-btn.active {
    background: var(--orange);
    color: var(--accent-text);
    border-color: var(--orange);
}
</style>
@endsection
