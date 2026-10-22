@extends('layouts.guest')
@section('title', $post->title . ' | Kennisbank')

@section('content')
<article id="article-view" style="padding-top: 130px; padding-bottom: 100px; background: var(--navy);">
    <div class="container" style="max-width: 860px;">
        
        {{-- Breadcrumbs & Back link --}}
        <div style="margin-bottom: 24px;">
            <a href="{{ route('blog') }}" class="blog-read" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px;">
                ← Terug naar kennisbank
            </a>
        </div>

        {{-- Meta Header --}}
        <header style="margin-bottom: 40px;">
            <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
                @if($post->category)
                    <span class="section-tag" style="margin-bottom: 0;">{{ $post->category->name }}</span>
                @endif
                <span style="font-size: 13px; color: var(--dim);">•</span>
                <time style="font-size: 13px; color: var(--muted);" datetime="{{ $post->published_at?->toIso8601String() }}">
                    {{ $post->published_at ? $post->published_at->format('d F Y') : $post->created_at->format('d F Y') }}
                </time>
                @if($post->user)
                    <span style="font-size: 13px; color: var(--dim);">•</span>
                    <span style="font-size: 13px; color: var(--muted);">Door {{ $post->user->name }}</span>
                @endif
            </div>

            <h1 class="section-title" style="font-size: clamp(32px, 4.5vw, 52px); line-height: 1.15; margin-bottom: 24px;">
                {{ $post->title }}
            </h1>

            @if($post->excerpt)
                <div class="article-excerpt-box">
                    <p>{{ $post->excerpt }}</p>
                </div>
            @endif
        </header>

        {{-- Structured Sections --}}
        <div class="article-content">
            @if(!empty($post->sections) && is_array($post->sections))
                @foreach($post->sections as $section)
                    <x-blocks.section :section="$section" />
                @endforeach
            @endif

            {{-- Fallback / Legacy body --}}
            @if($post->body)
                <div class="legacy-body" style="margin-top: 24px; color: var(--text); line-height: 1.8;">
                    {!! nl2br(e($post->body)) !!}
                </div>
            @endif
        </div>

        {{-- Footer actions & Share --}}
        <div style="margin-top: 60px; padding-top: 30px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <a href="{{ route('blog') }}" class="btn-outline">
                ← Alle artikelen bekijken
            </a>

            <a href="https://wa.me/?text={{ rawurlencode('Interessant artikel: ' . $post->title . ' - ' . url()->current()) }}" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="btn-primary" 
               style="background: #25D366; color: #fff;">
                Delen via WhatsApp 📲
            </a>
        </div>

        {{-- Related Posts --}}
        @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
            <div style="margin-top: 80px; padding-top: 40px; border-top: 1px solid var(--border);">
                <h3 style="font-size: 22px; color: #fff; margin-bottom: 24px;">Gerelateerde artikelen</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                    @foreach($relatedPosts as $related)
                        <div class="blog-card" onclick="window.location.href='{{ route('blog.show', $related->slug) }}'">
                            <div class="blog-body" style="padding: 20px;">
                                <span class="blog-cat">{{ $related->category?->name ?? 'Kennisbank' }}</span>
                                <h4 style="font-size: 15px; color: #fff; margin: 8px 0;">
                                    <a href="{{ route('blog.show', $related->slug) }}">{{ $related->title }}</a>
                                </h4>
                                <span class="blog-read" style="margin-top: 12px;">Lees verder →</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</article>

<style>
.article-excerpt-box {
    background: rgba(212,255,0,0.05);
    border-left: 3px solid var(--orange);
    padding: 20px 24px;
    border-radius: 0 12px 12px 0;
    margin-bottom: 36px;
}
.article-excerpt-box p {
    font-size: 17px;
    color: var(--text);
    line-height: 1.65;
    font-style: italic;
    margin: 0;
}
.article-section {
    margin-bottom: 44px;
}
.section-heading.text-h2 {
    font-size: clamp(24px, 3vw, 32px);
    font-weight: 800;
    color: #fff;
    margin-top: 36px;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
}
.section-heading.text-h3 {
    font-size: clamp(20px, 2.4vw, 24px);
    font-weight: 700;
    color: #fff;
    margin-top: 28px;
    margin-bottom: 12px;
}
.section-heading.text-h4 {
    font-size: 18px;
    font-weight: 700;
    color: var(--orange);
    margin-top: 20px;
    margin-bottom: 10px;
}
.section-figure {
    margin: 28px 0;
    border-radius: 12px;
    overflow: hidden;
    background: var(--navy-card);
    border: 1px solid var(--border);
}
.section-image {
    width: 100%;
    height: auto;
    display: block;
    object-fit: cover;
    max-height: 480px;
}
.section-caption {
    font-size: 13px;
    color: var(--dim);
    padding: 10px 16px;
    text-align: center;
    background: rgba(0,0,0,0.2);
    font-style: italic;
}
.section-prose {
    font-size: 16.5px;
    color: var(--muted);
    line-height: 1.85;
    letter-spacing: 0.1px;
}
.section-prose p {
    margin-bottom: 16px;
}
</style>
@endsection
