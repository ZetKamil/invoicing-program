<?php

namespace App\Models;

use App\Enums\CatalogItemStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogItem extends Model
{
    use HasFactory, HasUlids, HasTenant, SoftDeletes;

    protected $table = 'catalog_items';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility alias
        'category_id',
        'name',
        'slug',
        'sku',
        'status',
        'price_minor',
        'description',
        'attributes',
        'whatsapp_prefill_text',
        'views_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'     => CatalogItemStatus::class,
            'attributes' => 'array',
        ];
    }

    /**
     * Get price formatted in currency units with 2 decimal places.
     */
    public function getPriceFormattedAttribute(): ?string
    {
        if ($this->price_minor === null) {
            return null;
        }

        return number_format($this->price_minor / 100, 2, ',', ' ');
    }

    /**
     * Price in standard float units (e.g. 19.99) for forms and calculations.
     */
    public function getPriceAttribute(): ?float
    {
        return $this->price_minor !== null ? (float) ($this->price_minor / 100) : null;
    }

    public function setPriceAttribute(?float $value): void
    {
        $this->price_minor = $value !== null ? (int) bcmul((string) $value, '100', 0) : null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', CatalogItemStatus::AVAILABLE->value);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', '!=', CatalogItemStatus::HIDDEN->value);
    }
}
