<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasUlids, HasTenant;

    protected $table = 'categories';

    protected $fillable = [
        'tenant_id',
        'bedrijf_id', // Backward compatibility alias
        'type',
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'       => CategoryType::class,
            'sort_order' => 'integer',
        ];
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class, 'category_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    public function scopeCatalog(Builder $query): Builder
    {
        return $query->where('type', CategoryType::CATALOG->value);
    }

    public function scopeForPosts(Builder $query): Builder
    {
        return $query->where('type', CategoryType::POST->value);
    }
}
