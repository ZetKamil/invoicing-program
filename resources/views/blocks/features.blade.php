@php
    $data = $block['data'] ?? [];
    $title = $data['title'] ?? 'Onze Troeven';
    $subtitle = $data['subtitle'] ?? '';
    $items = $data['items'] ?? [];
@endphp

<section class="py-20 bg-slate-900 border-t border-slate-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">
                {{ $title }}
            </h2>
            @if($subtitle)
                <p class="text-slate-400 max-w-2xl mx-auto text-base sm:text-lg">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($items as $item)
                <div class="p-8 rounded-2xl bg-slate-800/50 border border-slate-700/60 hover:border-blue-500/50 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-xl mb-6 border border-blue-500/20">
                        ✓
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">
                        {{ $item['title'] ?? '' }}
                    </h3>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        {{ $item['description'] ?? '' }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
