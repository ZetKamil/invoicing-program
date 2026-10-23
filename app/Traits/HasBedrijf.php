<?php

namespace App\Traits;

/**
 * Trait HasBedrijf
 *
 * Backward-compatibility alias for HasTenant trait.
 * All models using HasBedrijf automatically benefit from TenantScope and 3NF multi-tenancy.
 */
trait HasBedrijf
{
    use HasTenant;
}
