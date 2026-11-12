<?php

namespace App\Enums;

/**
 * TenantPlan Enum
 *
 * Defines SaaS subscription levels available to tenants.
 * Controls the visibility of ERP and invoicing modules in Filament.
 */
enum TenantPlan: string
{
    /** Basic DIY self-builder package: catalog and blog only, blank layout. */
    case ZELFBOUWEN = 'zelfbouwen';

    /** Basic package: catalog and blog only. ERP is disabled. */
    case START = 'start';

    /** Pro package: catalog, blog, invoicing, and quotes. */
    case PRO = 'pro';

    /** Full enterprise package: full ERP, Peppol UBL, and advanced reporting. */
    case SCALE = 'scale';

    /**
     * Get human-readable label in Dutch for UI display.
     */
    public function label(): string
    {
        return match($this) {
            self::ZELFBOUWEN => 'Zelfbouwen (DIY)',
            self::START      => 'Start',
            self::PRO        => 'Pro',
            self::SCALE      => 'Scale',
        };
    }

    /**
     * Get plan description in Dutch for Super Admin panel.
     */
    public function description(): string
    {
        return match($this) {
            self::ZELFBOUWEN => 'Zelfbouw CMS (€199 setup). Wireframe layout voor zelfstandigen.',
            self::START      => 'Productcatalogus + Blog. Geen ERP-module.',
            self::PRO        => 'Catalogus + Blog + Facturatie en offertes.',
            self::SCALE      => 'Volledige toegang: ERP, Peppol UBL en geavanceerde rapportages.',
        };
    }

    /**
     * Determine whether this plan grants access to the ERP module.
     * Used in canViewAny() authorization hooks in Filament resources.
     */
    public function hasErpAccess(): bool
    {
        return match($this) {
            self::ZELFBOUWEN, self::START => false,
            self::PRO, self::SCALE        => true,
        };
    }
}
