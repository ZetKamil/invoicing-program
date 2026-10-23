@php
    $data = $block['data'] ?? [];
    $title = $data['title'] ?? 'Veelgestelde Vragen';
    $items = $data['items'] ?? [];
@endphp

<section class="py-20 bg-slate-950 border-t border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">
                {{ $title }}
            </h2>
        </div>

        <div class="space-y-4">
            @foreach($items as $item)
                <div class="p-6 rounded-xl bg-slate-900 border border-slate-800">
                    <h3 class="font-bold text-white text-lg mb-2">
                        {{ $item['question'] ?? '' }}
                    </h3>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        {{ $item['answer'] ?? '' }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
