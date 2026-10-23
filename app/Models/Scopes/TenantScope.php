<?php

namespace App\Models\Scopes;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the tenant scoping to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = self::resolveCurrentTenantId();

        if ($tenantId !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }

    /**
     * Resolve the active Tenant ID from context (Filament, Session, or User).
     */
    public static function resolveCurrentTenantId(): ?string
    {
        // 1. Check if Filament has an active tenant selected
        try {
            if (class_exists(Filament::class) && Filament::hasTenancy()) {
                $filamentTenant = Filament::getTenant();
                if ($filamentTenant) {
                    return $filamentTenant->id ?? (string) $filamentTenant;
                }
            }
        } catch (\Throwable $e) {
            // Ignore during early boot or console commands without Filament context
        }

        // 2. Check explicit session override (used during impersonation / tenant switching)
        if (session()->has('current_tenant_id')) {
            return session('current_tenant_id');
        }

        // 3. Fallback to authenticated user's current tenant
        if (Auth::check()) {
            $user = Auth::user();

            // If user is super-admin without explicit tenant selected, do not restrict queries
            if ($user->is_super_admin && !session()->has('current_tenant_id')) {
                return null;
            }

            return $user->tenant_id;
        }

        return null;
    }
}
