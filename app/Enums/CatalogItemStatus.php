<?php

namespace App\Enums;

/**
 * CatalogItemStatus Enum
 *
 * Status pozycji w katalogu. Celowo branżowo-neutralny:
 * - 'available'    = dostępny kot / produkt / usługa
 * - 'reserved'     = zarezerwowany (oczekuje na finalizację)
 * - 'sold_adopted' = sprzedany / adoptowany (archiwum)
 * - 'hidden'       = ukryty (draft, nie pokazuje się na froncie)
 *
 * Wzorzec Senior: Enum jako State Machine — każda pozycja
 * przechodzi przez stany w logiczny sposób i nigdy nie
 * zawiera nieprawidłowej wartości (w przeciwieństwie do stringa).
 */
enum CatalogItemStatus: string
{
    case AVAILABLE    = 'available';
    case RESERVED     = 'reserved';
    case SOLD_ADOPTED = 'sold_adopted';
    case HIDDEN       = 'hidden';

    /**
     * Zwraca czytelną etykietę do wyświetlenia w UI i panelu.
     */
    public function label(): string
    {
        return match($this) {
            self::AVAILABLE    => 'Dostępny',
            self::RESERVED     => 'Zarezerwowany',
            self::SOLD_ADOPTED => 'Sprzedany / Adoptowany',
            self::HIDDEN       => 'Ukryty',
        };
    }

    /**
     * Zwraca kolor znacznika (badge) Filament dla danego statusu.
     * Używane przez ->badge()->color(fn(CatalogItemStatus $s) => $s->badgeColor())
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
     * Czy pozycja jest widoczna publicznie na stronie?
     * Używane w query scope i na froncie.
     */
    public function isPublic(): bool
    {
        return $this === self::AVAILABLE;
    }
}
