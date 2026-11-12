<?php

namespace App\Traits;

use App\Models\Bedrijf;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenant
{
    /**
     * Boot the HasTenant trait.
     */
    public static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->tenant_id)) {
                $resolvedId = TenantScope::resolveCurrentTenantId();
                if ($resolvedId) {
                    $model->tenant_id = $resolvedId;
                }
            }
        });
    }

    /**
     * Get the tenant that owns the model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Backward-compatibility relation for existing code referencing $model->bedrijf.
     */
    public function bedrijf(): BelongsTo
    {
        return $this->belongsTo(Bedrijf::class, 'tenant_id');
    }

    /**
     * Backward-compatibility accessor for $model->bedrijf_id.
     */
    public function getBedrijfIdAttribute(): ?string
    {
        return $this->attributes['tenant_id'] ?? null;
    }

    /**
     * Backward-compatibility mutator for $model->bedrijf_id.
     */
    public function setBedrijfIdAttribute(?string $value): void
    {
        $this->attributes['tenant_id'] = $value;
    }
}
