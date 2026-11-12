<?php

namespace Database\Factories;

use App\Models\Bedrijf;

/**
 * Class BedrijfFactory
 *
 * Backward-compatibility alias for TenantFactory.
 */
class BedrijfFactory extends TenantFactory
{
    protected $model = Bedrijf::class;
}
