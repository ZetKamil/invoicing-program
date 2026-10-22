<?php

namespace App\Models;

use App\Enums\CatalogItemStatus;
use App\Traits\HasBedrijf;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'bedrijf_id', 'category_id',
    'name', 'slug', 'status',
    'price_minor', 'description',
    'attributes', 'whatsapp_prefill_text',
])]
class CatalogItem extends Model
{
    use HasFactory, HasUlids, HasBedrijf, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'     => CatalogItemStatus::class,
            'attributes' => 'array',   // JSON -> array automatycznie przy odczycie
        ];
    }

    // ──────────────────────────────────────────────
    // Accessors (wyliczone atrybuty)
    // ──────────────────────────────────────────────

    /**
     * Zwraca cenę w złotych/euro jako string BCMath z 2 miejscami po przecinku.
     * Przeliczenie: price_minor (w groszach) / 100 = cena w walucie.
     *
     * Wzorzec Senior: prezentacja ceny ZAWSZE z Accessorem, nigdy surowo w widoku.
     * Eliminuje rozrzucone "/ 100" po całym kodzie.
     */
    public function getPriceFormattedAttribute(): ?string
    {
        if ($this->price_minor === null) {
            return null;
        }

        return number_format($this->price_minor / 100, 2, ',', ' ');
    }

    // ──────────────────────────────────────────────
    // Relacje
    // ──────────────────────────────────────────────

    /**
     * Kategoria do której należy ta pozycja.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ──────────────────────────────────────────────
    // Query Scopes (filtry wielokrotnego użytku)
    // ──────────────────────────────────────────────

    /**
     * Tylko publicznie widoczne pozycje (status = available).
     * Użycie: CatalogItem::available()->get()
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', CatalogItemStatus::AVAILABLE->value);
    }

    /**
     * Wyklucza ukryte pozycje — przydatne w publicznym API i froncie.
     * Użycie: CatalogItem::visible()->get()
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', '!=', CatalogItemStatus::HIDDEN->value);
    }
}
