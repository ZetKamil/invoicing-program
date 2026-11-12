@extends('layouts.guest')
@section('title', $item->name . ' | Aanbod')

@section('content')
<section id="catalog-detail" style="padding-top: 130px; padding-bottom: 100px; background: var(--navy);">
    <div class="container">
        
        {{-- Breadcrumbs & Back link --}}
        <div style="margin-bottom: 24px;">
            <a href="{{ route('catalog.index') }}" class="blog-read" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px;">
                ← Terug naar aanbod
            </a>
        </div>

        <div class="catalog-detail-layout">
            
            {{-- Main Column --}}
            <div class="detail-main">
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 16px;">
                    @if($item->category)
                        <span class="section-tag" style="margin-bottom: 0;">{{ $item->category->name }}</span>
                    @endif
                    <span class="status-badge status-{{ $item->status->value }}">
                        {{ $item->status->label() }}
                    </span>
                </div>

                <h1 class="section-title" style="font-size: clamp(32px, 4vw, 48px); margin-bottom: 16px;">
                    {{ $item->name }}
                </h1>

                <div class="detail-price-mobile">
                    € {{ $item->price_formatted }}
                </div>

                @if($item->description)
                    <div class="detail-description">
                        <h3 style="font-size: 18px; color: #fff; margin-bottom: 12px;">Beschrijving</h3>
                        <p style="color: var(--muted); font-size: 16px; line-height: 1.8;">
                            {!! nl2br(e($item->description)) !!}
                        </p>
                    </div>
                @endif

                {{-- Dynamic Attributes Table --}}
                @if(!empty($item->attributes) && is_array($item->attributes))
                    <div class="attributes-card" style="margin-top: 36px;">
                        <h3 style="font-size: 18px; color: #fff; margin-bottom: 20px;">Kenmerken & Specificaties</h3>
                        <div class="attrs-grid">
                            @foreach($item->attributes as $key => $val)
                                <div class="attr-row">
                                    <span class="attr-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                    <span class="attr-value">
                                        @if(is_array($val))
                                            {{ implode(', ', $val) }}
                                        @elseif(is_bool($val))
                                            {{ $val ? 'Ja' : 'Nee' }}
                                        @else
                                            {{ $val }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar Sticky CTA --}}
            <div class="detail-sidebar">
                <div class="sticky-inquiry-box">
                    <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">
                        Prijs
                    </div>
                    <div class="sidebar-price">
                        € {{ $item->price_formatted }}
                    </div>

                    <div style="margin: 20px 0; padding: 12px 16px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px; color: var(--muted);">Status:</span>
                        <span class="status-badge status-{{ $item->status->value }}">{{ $item->status->label() }}</span>
                    </div>

                    {{-- WhatsApp CTA --}}
                    <a href="{{ $whatsAppLink }}" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="whatsapp-cta-btn">
                        <span>📲 Direct via WhatsApp</span>
                        <small style="font-size: 11px; font-weight: normal; opacity: 0.9; display: block;">Open chat met vooraf ingevulde vraag</small>
                    </a>

                    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: var(--muted);">
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--orange);">✓</span> Snelle reactie gegarandeerd
                            </li>
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--orange);">✓</span> Volledige documentatie & factuur
                            </li>
                            <li style="display: flex; align-items: center; gap: 8px;">
                                <span style="color: var(--orange);">✓</span> Vrijblijvende bezichtiging of advies
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        {{-- Similar items --}}
        @if(isset($similarItems) && $similarItems->isNotEmpty())
            <div style="margin-top: 80px; padding-top: 40px; border-top: 1px solid var(--border);">
                <h3 style="font-size: 22px; color: #fff; margin-bottom: 24px;">Bekijk ook in deze categorie</h3>
                <div class="catalog-grid">
                    @foreach($similarItems as $sim)
                        <div class="catalog-card">
                            <div class="catalog-card-body">
                                <span class="catalog-cat">{{ $sim->category?->name }}</span>
                                <h4 class="catalog-title" style="margin-top: 8px; font-size: 17px;">
                                    <a href="{{ route('catalog.show', $sim->slug) }}">{{ $sim->name }}</a>
                                </h4>
                                <div class="catalog-card-footer">
                                    <div class="catalog-price" style="font-size: 18px;">
                                        {{ $sim->price_formatted }}
                                    </div>
                                    <a href="{{ route('catalog.show', $sim->slug) }}" class="btn-outline btn-sm">
                                        Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>

<style>
.catalog-detail-layout {
    display: grid;
    grid-template-columns: 7fr 5fr;
    gap: 48px;
    align-items: start;
}
@media (max-width: 900px) {
    .catalog-detail-layout {
        grid-template-columns: 1fr;
    }
}
.detail-price-mobile {
    display: none;
    font-family: 'Outfit', sans-serif;
    font-size: 28px;
    font-weight: 800;
    color: var(--orange);
    margin-bottom: 24px;
}
@media (max-width: 900px) {
    .detail-price-mobile { display: block; }
}

.detail-description {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px;
    margin-top: 28px;
}
.attributes-card {
    background: var(--navy-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 28px;
}
.attrs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}
.attr-row {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.04);
    padding: 12px 16px;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.attr-label {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--dim);
}
.attr-value {
    font-size: 14.5px;
    font-weight: 600;
    color: #fff;
}

.sticky-inquiry-box {
    position: sticky;
    top: 90px;
    background: var(--navy-card);
    border: 1px solid var(--border-o);
    border-radius: 16px;
    padding: 32px;
}
.sidebar-price {
    font-family: 'Outfit', sans-serif;
    font-size: 38px;
    font-weight: 800;
    color: var(--orange);
    line-height: 1;
}
.whatsapp-cta-btn {
    display: block;
    width: 100%;
    background: #25D366;
    color: #fff;
    text-align: center;
    padding: 16px 20px;
    border-radius: 10px;
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 800;
    text-decoration: none;
    transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 18px rgba(37, 211, 102, 0.25);
}
.whatsapp-cta-btn:hover {
    background: #20bd5a;
    transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(37, 211, 102, 0.35);
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
</style>
@endsection
