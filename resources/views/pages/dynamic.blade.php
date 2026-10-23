@extends('layouts.guest')

@section('title', ($page->seo_meta['meta_title'] ?? $page->title) . ' | ' . ($tenant->name ?? 'KMO-Flow'))

@section('content')
    @if(empty($page->blocks))
        <div class="py-32 text-center text-slate-400 bg-slate-900">
            <h1 class="text-3xl font-bold text-white mb-4">{{ $page->title }}</h1>
            <p>Deze pagina heeft nog geen visuele blokken geconfigureerd.</p>
        </div>
    @else
        @foreach($page->blocks as $block)
            @php
                $type = $block['type'] ?? 'hero';
                $viewName = "blocks.{$type}";
            @endphp

            @if(view()->exists($viewName))
                @include($viewName, ['block' => $block, 'tenant' => $tenant, 'page' => $page])
            @endif
        @endforeach
    @endif
@endsection
