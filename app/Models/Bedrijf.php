<?php

namespace App\Models;

/**
 * Class Bedrijf
 *
 * Backward-compatibility alias for Tenant model.
 * In KMO-Flow v2.1 3NF Architecture, the primary domain model is Tenant (table: tenants).
 * Bedrijf extends Tenant so that existing services, resources, and tests continue to work without modification.
 */
class Bedrijf extends Tenant
{
    // Inherits table 'tenants', relationships, and methods from Tenant.
}
