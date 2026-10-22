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
    // Relacje
    // ──────────────────────────────────────────────

    /**
     * Pozycje katalogu należące do tej kategorii.
     */
    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class);
    }

    // ──────────────────────────────────────────────
    // Query Scopes (filtry wielokrotnego użytku)
    // ──────────────────────────────────────────────

    /**
     * Filtruje tylko kategorie katalogu produktów.
     * Użycie: Category::catalog()->get()
     */
    public function scopeCatalog(Builder $query): Builder
    {
        return $query->where('type', CategoryType::CATALOG->value);
    }

    /**
     * Filtruje tylko kategorie bloga.
     * Użycie: Category::forPosts()->get()
     */
    public function scopeForPosts(Builder $query): Builder
    {
        return $query->where('type', CategoryType::POST->value);
    }
}
