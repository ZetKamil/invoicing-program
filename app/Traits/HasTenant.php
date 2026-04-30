<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasTenant
{
    /**
     * Boot the trait.
     */
    public static function bootHasTenant(): void
    {
        // For simplicity in testing/branch state, we'll just auto-assign if not set.
        // If TenantScope was required, we would add it here.
        
        static::creating(function (Model $model) {
            if (Auth::check() && ! $model->tenant_id) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }
}
