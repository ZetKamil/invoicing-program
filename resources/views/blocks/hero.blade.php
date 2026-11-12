@php
    $data = $block['data'] ?? [];
    $badge = $data['badge'] ?? null;
    $title = $data['title'] ?? ($tenant->name ?? 'Welkom');
    $subtitle = $data['subtitle'] ?? '';
    $primaryCtaText = $data['primary_cta_text'] ?? 'Neem Contact Op';
    $primaryCtaUrl = $data['primary_cta_url'] ?? '#contact';
    $secondaryCtaText = $data['secondary_cta_text'] ?? null;
    $secondaryCtaUrl = $data['secondary_cta_url'] ?? '#aanbod';
@endphp

<section class="py-20 bg-gradient-to-b from-slate-900 via-slate-850 to-slate-900 text-white relative overflow-hidden">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        @if($badge)
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider bg-blue-500/10 text-blue-400 border border-blue-500/20 mb-6">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                {{ $badge }}
            </div>
        @endif

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-6 leading-tight">
            {{ $title }}
        </h1>

        @if($subtitle)
            <p class="max-w-2xl mx-auto text-lg sm:text-xl text-slate-300 mb-10 leading-relaxed">
                {{ $subtitle }}
            </p>
        @endif

        <div class="flex flex-wrap items-center justify-center gap-4">
            @if($primaryCtaText)
                <a href="{{ $primaryCtaUrl }}" class="px-8 py-3.5 rounded-xl font-bold text-base bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-500/25 transition-all duration-200">
                    {{ $primaryCtaText }}
                </a>
            @endif

            @if($secondaryCtaText)
                <a href="{{ $secondaryCtaUrl }}" class="px-8 py-3.5 rounded-xl font-bold text-base bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all duration-200">
                    {{ $secondaryCtaText }}
                </a>
            @endif
        </div>
    </div>
</section>
