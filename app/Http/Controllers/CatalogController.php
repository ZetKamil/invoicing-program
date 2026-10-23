<?php

namespace App\Http\Controllers;

use App\Enums\CatalogItemStatus;
use App\Enums\CategoryType;
use App\Models\Bedrijf;
use App\Models\CatalogItem;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display a public listing of available and reserved catalog items.
     */
    public function index(Request $request): View
    {
        $bedrijf = Bedrijf::where('custom_domain', $request->getHost())->first() ?? Bedrijf::first();

        $query = CatalogItem::query()
            ->with('category')
            ->whereIn('status', [CatalogItemStatus::AVAILABLE, CatalogItemStatus::RESERVED])
            ->latest();

        if ($bedrijf) {
            $query->where('tenant_id', $bedrijf->id);
        }

        $activeCategory = null;
        if ($request->filled('categorie')) {
            $categorySlug = $request->query('categorie');
            $activeCategory = Category::where('slug', $categorySlug)
                ->where('type', CategoryType::CATALOG)
                ->first();

            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        $items = $query->paginate(12)->withQueryString();

        $categories = Category::query()
            ->where('type', CategoryType::CATALOG)
            ->when($bedrijf, fn ($q) => $q->where('tenant_id', $bedrijf->id))
            ->whereHas('catalogItems', fn ($q) => $q->whereIn('status', [CatalogItemStatus::AVAILABLE, CatalogItemStatus::RESERVED]))
            ->withCount(['catalogItems' => fn ($q) => $q->whereIn('status', [CatalogItemStatus::AVAILABLE, CatalogItemStatus::RESERVED])])
            ->get();

        return view('pages.catalog.index', compact('items', 'categories', 'activeCategory', 'bedrijf'));
    }

    /**
     * Display a single catalog item detail page with attributes and WhatsApp inquiry action.
     */
    public function show(Request $request, string $slug): View
    {
        $item = CatalogItem::query()
            ->with('category')
            ->where('slug', $slug)
            ->whereIn('status', [CatalogItemStatus::AVAILABLE, CatalogItemStatus::RESERVED])
            ->firstOrFail();

        $prefillText = $item->whatsapp_prefill_text 
            ?: "Hallo! Ik heb interesse in '{$item->name}'. Kunt u mij meer informatie bezorgen?";

        $whatsAppLink = "https://wa.me/?text=" . rawurlencode($prefillText);

        $similarItems = CatalogItem::query()
            ->where('tenant_id', $item->tenant_id)
            ->where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->whereIn('status', [CatalogItemStatus::AVAILABLE, CatalogItemStatus::RESERVED])
            ->latest()
            ->take(3)
            ->get();

        return view('pages.catalog.show', compact('item', 'whatsAppLink', 'similarItems'));
    }
}
