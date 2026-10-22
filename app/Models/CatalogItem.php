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
            'attributes' => 'array',   // JSON cast to array automatically
        ];
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get price formatted in currency units with 2 decimal places.
     * Calculation: price_minor (cents) / 100 = currency value.
     */
    public function getPriceFormattedAttribute(): ?string
    {
        if ($this->price_minor === null) {
            return null;
        }

        return number_format($this->price_minor / 100, 2, ',', ' ');
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Category that this catalog item belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ──────────────────────────────────────────────
    // Query Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope query to publicly available items only.
     * Usage: CatalogItem::available()->get()
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', CatalogItemStatus::AVAILABLE->value);
    }

    /**
     * Exclude hidden draft items — useful for frontend and public API.
     * Usage: CatalogItem::visible()->get()
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', '!=', CatalogItemStatus::HIDDEN->value);
    }
}
