<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Traits\HasBedrijf;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['bedrijf_id', 'type', 'name', 'slug'])]
class Category extends Model
{
    use HasFactory, HasUlids, HasBedrijf;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Catalog items associated with this category.
     */
    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class);
    }

    // ──────────────────────────────────────────────
    // Query Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope query to catalog categories only.
     * Usage: Category::catalog()->get()
     */
    public function scopeCatalog(Builder $query): Builder
    {
        return $query->where('type', CategoryType::CATALOG->value);
    }

    /**
     * Scope query to blog post categories only.
     * Usage: Category::forPosts()->get()
     */
    public function scopeForPosts(Builder $query): Builder
    {
        return $query->where('type', CategoryType::POST->value);
    }
}
