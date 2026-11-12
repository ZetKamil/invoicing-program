<?php

namespace App\Enums;

/**
 * CatalogItemStatus Enum
 *
 * Status machine for items in the universal catalog.
 * Represents industry-neutral lifecycle states.
 */
enum CatalogItemStatus: string
{
    case AVAILABLE    = 'available';
    case RESERVED     = 'reserved';
    case SOLD_ADOPTED = 'sold_adopted';
    case HIDDEN       = 'hidden';

    /**
     * Get human-readable label in Dutch for UI display.
     */
    public function label(): string
    {
        return match($this) {
            self::AVAILABLE    => 'Beschikbaar',
            self::RESERVED     => 'Gereserveerd',
            self::SOLD_ADOPTED => 'Verkocht / Geadopteerd',
            self::HIDDEN       => 'Verborgen',
        };
    }

    /**
     * Get badge color for Filament UI badge rendering.
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::AVAILABLE    => 'success',
            self::RESERVED     => 'warning',
            self::SOLD_ADOPTED => 'gray',
            self::HIDDEN       => 'danger',
        };
    }

    /**
     * Determine if the catalog item is publicly visible.
     */
    public function isPublic(): bool
    {
        return $this === self::AVAILABLE;
    }
}
