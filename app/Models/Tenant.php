<?php

namespace App\Models;

use App\Enums\TenantPlan;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    /** @use HasFactory<\Database\Factories\BedrijfFactory> */
    use HasFactory, HasUlids;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'custom_domain',
        'plan',
        'status',
        'vat_number',
        'kbo_number',
        'peppol_endpoint_id',
        'peppol_id', // Backward-compatibility alias
        'iban',
        'bic',
        'settings',
        'stripe_customer_id',
        'stripe_id', // Backward-compatibility alias
        'stripe_subscription_id',
        'stripe_subscription_status', // Backward-compatibility alias
        'active_packages', // Backward-compatibility alias
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan'     => TenantPlan::class,
            'settings' => 'array',
        ];
    }

    /* -------------------------------------------------------------------------
     | Backward Compatibility Accessors & Mutators
     | ------------------------------------------------------------------------- */

    public function getPeppolIdAttribute(): ?string
    {
        return $this->attributes['peppol_endpoint_id'] ?? null;
    }

    public function setPeppolIdAttribute(?string $value): void
    {
        $this->attributes['peppol_endpoint_id'] = $value;
    }

    public function getStripeIdAttribute(): ?string
    {
        return $this->attributes['stripe_customer_id'] ?? null;
    }

    public function setStripeIdAttribute(?string $value): void
    {
        $this->attributes['stripe_customer_id'] = $value;
    }

    public function getStripeSubscriptionStatusAttribute(): string
    {
        return $this->settings['stripe_subscription_status'] ?? $this->status ?? 'active';
    }

    public function setStripeSubscriptionStatusAttribute(string $value): void
    {
        $settings = $this->settings ?? [];
        $settings['stripe_subscription_status'] = $value;
        $this->settings = $settings;
        if (in_array($value, ['active', 'past_due', 'canceled', 'suspended'])) {
            $this->attributes['status'] = $value;
        }
    }

    public function getActivePackagesAttribute(): array
    {
        return $this->settings['active_packages'] ?? [];
    }

    public function setActivePackagesAttribute(array $value): void
    {
        $settings = $this->settings ?? [];
        $settings['active_packages'] = $value;
        $this->settings = $settings;
    }

    /* -------------------------------------------------------------------------
     | Business Logic & Multi-Tenant Payment Helpers
     | ------------------------------------------------------------------------- */

    /**
     * Determine if the tenant has access to the ERP module (invoices, quotes, Peppol).
     */
    public function hasErpAccess(): bool
    {
        return $this->plan?->hasErpAccess() ?? false;
    }

    /**
     * Resolve Stripe Secret Key for this tenant.
     * Returns tenant's dedicated key if configured, or falls back to platform default.
     */
    public function getStripeSecretKey(): ?string
    {
        return $this->settings['payment_gateways']['stripe']['secret_key'] 
            ?? config('services.stripe.secret');
    }

    /**
     * Resolve Stripe Publishable Key for this tenant.
     */
    public function getStripePublishableKey(): ?string
    {
        return $this->settings['payment_gateways']['stripe']['publishable_key'] 
            ?? config('services.stripe.key');
    }

    /* -------------------------------------------------------------------------
     | Relationships (3NF Normalized Schema)
     | ------------------------------------------------------------------------- */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'tenant_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'tenant_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'tenant_id');
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(CatalogItem::class, 'tenant_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'tenant_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'tenant_id');
    }

    public function commercialDocuments(): HasMany
    {
        return $this->hasMany(CommercialDocument::class, 'tenant_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'tenant_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'tenant_id');
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'tenant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(DailyMetric::class, 'tenant_id');
    }

    public function aiGenerationLogs(): HasMany
    {
        return $this->hasMany(AiGenerationLog::class, 'tenant_id');
    }

    public function aiSchedule(): HasOne
    {
        return $this->hasOne(AiSchedule::class, 'tenant_id');
    }
}
