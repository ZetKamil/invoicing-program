@extends('layouts.guest')
@section('title', 'Aanbod & Diensten | ' . ($bedrijf->name ?? 'TransDigit Master'))

@section('content')
<section id="catalog-header" style="padding-top: 140px; padding-bottom: 40px; background: var(--navy-mid);">
    <div class="container">
        <p class="section-tag">Catalogus & Aanbod</p>
        <h1 class="section-title">Ontdek ons actuele aanbod</h1>
        <p class="section-sub">Bekijk onze beschikbare posities, specificaties en neem direct contact op voor reservatie.</p>

        @if($categories->isNotEmpty())
            <div class="category-pills" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 30px;">
                <a href="{{ route('catalog.index') }}" class="pill-btn {{ is_null($activeCategory) ? 'active' : '' }}">
                    Alles weergeven
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('catalog.index', ['categorie' => $category->slug]) }}" 
                       class="pill-btn {{ $activeCategory?->id === $category->id ? 'active' : '' }}">
                        {{ $category->name }} ({{ $category->catalog_items_count }})
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section id="catalog-content" style="padding: 60px 0 100px; background: var(--navy);">
    <div class="container">
        @if($items->isEmpty())
            <div class="empty-state" style="text-align: center; padding: 80px 20px; background: var(--navy-card); border: 1px solid var(--border); border-radius: 16px;">
                <div style="font-size: 40px; margin-bottom: 16px;">📦</div>
                <h3 style="color: #fff; font-size: 20px; margin-bottom: 8px;">Geen items beschikbaar</h3>
                <p style="color: var(--muted); font-size: 14px;">Er zijn momenteel geen actieve items gevonden in deze categorie.</p>
                @if($activeCategory)
                    <a href="{{ route('catalog.index') }}" class="btn-outline" style="margin-top: 20px; display: inline-flex;">
                        Alle items bekijken
                    </a>
                @endif
            </div>
        @else
            <div class="catalog-grid">
                @foreach($items as $item)
                    <article class="catalog-card">
                        <div class="catalog-card-body">
                            {{-- Header meta --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                                <span class="catalog-cat">{{ $item->category?->name ?? 'Algemeen' }}</span>
                                <span class="status-badge status-{{ $item->status->value }}">
                                    {{ $item->status->label() }}
                                </span>
                            </div>

                            <h2 class="catalog-title">
                                <a href="{{ route('catalog.show', $item->slug) }}">{{ $item->name }}</a>
                            </h2>

                            @if($item->description)
                                <p class="catalog-desc">{{ Str::limit($item->description, 100) }}</p>
                            @endif

                            {{-- Dynamic attributes preview --}}
                            @if(!empty($item->attributes) && is_array($item->attributes))
                                <div class="catalog-attrs">
                                    @foreach(array_slice($item->attributes, 0, 3) as $key => $val)
                                        <div class="attr-pill">
                                            <span class="attr-k">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span class="attr-v">{{ is_array($val) ? implode(', ', $val) : $val }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Price & CTA --}}
                            <div class="catalog-card-footer">
                                <div class="catalog-price">
                                    € {{ $item->price_formatted }}
                                </div>
                                <a href="{{ route('catalog.show', $item->slug) }}" class="btn-outline btn-sm">
                                    Details →
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 48px;">
                {{ $items->links() }}
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

.catalog-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}
@media (max-width: 1024px) {
    .catalog-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .catalog-grid { grid-template-columns: 1fr; }
}

.catalog-card {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: border-color 0.3s, transform 0.3s;
}
.catalog-card:hover {
    border-color: var(--border-o);
    transform: translateY(-4px);
}
.catalog-card-body {
    padding: 28px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.catalog-cat {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--muted);
}
.status-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 100px;
}
.status-available {
    background: rgba(34, 197, 94, 0.12);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}
.status-reserved {
    background: rgba(234, 179, 8, 0.12);
    color: #facc15;
    border: 1px solid rgba(234, 179, 8, 0.3);
}

.catalog-title {
    font-size: 19px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
    line-height: 1.3;
}
.catalog-desc {
    font-size: 13.5px;
    color: var(--muted);
    line-height: 1.6;
    margin-bottom: 16px;
}
.catalog-attrs {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 20px;
    background: rgba(255,255,255,0.02);
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.04);
}
.attr-pill {
    font-size: 12.5px;
    display: flex;
    justify-content: space-between;
}
.attr-k {
    color: var(--dim);
}
.attr-v {
    color: #fff;
    font-weight: 500;
}
.catalog-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 18px;
    border-top: 1px solid var(--border);
}
.catalog-price {
    font-family: 'Outfit', sans-serif;
    font-size: 22px;
    font-weight: 800;
    color: var(--orange);
}
.btn-sm {
    padding: 8px 16px;
    font-size: 12.5px;
}
</style>
@endsection
