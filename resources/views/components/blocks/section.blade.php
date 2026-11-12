@props(['section'])

@php
    $heading = $section['heading'] ?? null;
    $level = in_array($section['level'] ?? 'h2', ['h2', 'h3', 'h4']) ? $section['level'] : 'h2';
    $content = $section['content'] ?? '';
    $imageUrl = $section['image_url'] ?? null;
    $imageCaption = $section['image_caption'] ?? null;
@endphp

<div class="article-section" id="section-{{ \Illuminate\Support\Str::slug($heading ?? 'content') }}">
    @if($heading)
        @if($level === 'h3')
            <h3 class="section-heading text-h3">{{ $heading }}</h3>
        @elseif($level === 'h4')
            <h4 class="section-heading text-h4">{{ $heading }}</h4>
        @else
            <h2 class="section-heading text-h2">{{ $heading }}</h2>
        @endif
    @endif

    @if($imageUrl)
        <figure class="section-figure">
            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $imageCaption ?: ($heading ?: 'Illustratie bij artikel') }}" 
                class="section-image" 
                loading="lazy"
            />
            @if($imageCaption)
                <figcaption class="section-caption">{{ $imageCaption }}</figcaption>
            @endif
        </figure>
    @endif

    @if($content)
        <div class="section-prose">
            {!! nl2br(e($content)) !!}
        </div>
    @endif
</div>
