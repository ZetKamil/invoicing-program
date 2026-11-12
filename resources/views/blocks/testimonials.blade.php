@php
    $data = $block['data'] ?? [];
    $title = $data['title'] ?? 'Ervaringen van Onze Klanten';
    $items = $data['items'] ?? [];
@endphp

<section class="py-20 bg-slate-900 border-t border-slate-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">
                {{ $title }}
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($items as $item)
                <div class="p-8 rounded-2xl bg-slate-850 border border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-1 text-amber-400 mb-4 text-lg">
                            @for($i = 0; $i < ($item['rating'] ?? 5); $i++)
                                ★
                            @endfor
                        </div>
                        <p class="text-slate-200 text-base italic leading-relaxed mb-6">
                            "{{ $item['quote'] ?? '' }}"
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-800">
                        <div class="font-bold text-white text-sm">
                            {{ $item['author'] ?? '' }}
                        </div>
                        @if(!empty($item['role']))
                            <div class="text-xs text-slate-400">
                                {{ $item['role'] }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
