<?php

namespace App\Models;

use App\Enums\TenantPlan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'slug', 'plan', 'custom_domain',
    'vat_number', 'peppol_id',
    'stripe_id', 'stripe_subscription_id', 'stripe_subscription_status',
    'settings', 'active_packages',
])]
class Bedrijf extends Model
{
    /** @use HasFactory<\Database\Factories\BedrijfFactory> */
    use HasFactory, HasUlids;

    protected $table = 'bedrijven';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan'            => TenantPlan::class,
            'settings'        => 'array',
            'active_packages' => 'array',
        ];
    }

    /**
     * Determine if the tenant has access to the ERP module (invoices, quotes, Peppol).
     * Feature flag delegating subscription logic directly to the TenantPlan enum.
     */
    public function hasErpAccess(): bool
    {
        return $this->plan?->hasErpAccess() ?? false;
    }

    /**
     * Get the users associated with the bedrijf.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class);
    }
}
