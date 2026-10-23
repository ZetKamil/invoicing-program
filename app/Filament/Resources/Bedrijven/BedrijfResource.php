<?php

namespace App\Filament\Resources\Bedrijven;

use App\Filament\Resources\Tenants\TenantResource;

/**
 * Class BedrijfResource
 *
 * Backward-compatibility alias for TenantResource.
 */
class BedrijfResource extends TenantResource
{
    /**
     * Prevent duplicate navigation item since TenantResource is the authoritative resource.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
