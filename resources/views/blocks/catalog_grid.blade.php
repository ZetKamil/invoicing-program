@php
    $data = $block['data'] ?? [];
    $title = $data['title'] ?? 'Ons Aanbod';
    $subtitle = $data['subtitle'] ?? '';
    $limit = $data['limit'] ?? 6;
    
    $catalogItems = \App\Models\CatalogItem::where('tenant_id', $tenant->id)
        ->whereIn('status', ['available', 'reserved'])
        ->latest()
        ->take($limit)
        ->get();
@endphp

<section id="aanbod" class="py-20 bg-slate-950 border-t border-slate-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-14">
            <div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">
                    {{ $title }}
                </h2>
                @if($subtitle)
                    <p class="text-slate-400 text-base max-w-xl">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
            <a href="{{ route('catalog.index') }}" class="mt-4 sm:mt-0 inline-flex items-center text-sm font-semibold text-blue-400 hover:text-blue-300">
                Volledige catalogus bekijken &rarr;
            </a>
        </div>

        @if($catalogItems->isEmpty())
            <div class="text-center py-12 px-6 rounded-2xl bg-slate-900 border border-slate-800 text-slate-400">
                <p>Er zijn momenteel geen artikelen gepubliceerd.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($catalogItems as $item)
                    <div class="group rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden hover:border-slate-700 transition-all duration-300 flex flex-col">
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->status?->value === 'reserved' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }}">
                                    {{ $item->status?->value === 'reserved' ? 'Gereserveerd' : 'Beschikbaar' }}
                                </span>
                                @if($item->price_formatted)
                                    <span class="text-lg font-extrabold text-white">
                                        € {{ $item->price_formatted }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">
                                        Prijs op aanvraag
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-400 transition-colors">
                                <a href="{{ route('catalog.show', $item->slug) }}">
                                    {{ $item->name }}
                                </a>
                            </h3>

                            @if($item->description)
                                <p class="text-slate-400 text-sm line-clamp-3 mb-4 flex-1">
                                    {{ Str::limit(strip_tags($item->description), 120) }}
                                </p>
                            @endif

                            <div class="pt-4 mt-auto border-t border-slate-800 flex items-center justify-between">
                                <a href="{{ route('catalog.show', $item->slug) }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300">
                                    Details & WhatsApp &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
