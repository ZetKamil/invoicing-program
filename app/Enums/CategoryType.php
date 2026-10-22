<?php

namespace App\Enums;

/**
 * CategoryType Enum
 *
 * Określa do której części aplikacji należy dana kategoria.
 * Jedna tabela 'categories' obsługuje zarówno katalog produktów
 * jak i kategorie bloga — typ rozróżnia je w zapytaniach.
 *
 * Wzorzec Senior: Single Table z discriminatorem zamiast 2 osobnych tabel
 * (catalog_categories + post_categories) — eliminuje redundancję schema.
 */
enum CategoryType: string
{
    /** Kategoria używana do grupowania pozycji katalogu (produktów, kotów, usług). */
    case CATALOG = 'catalog';

    /** Kategoria używana do grupowania postów blogowych. */
    case POST = 'post';

    /**
     * Zwraca czytelną etykietę do UI.
     */
    public function label(): string
    {
        return match($this) {
            self::CATALOG => 'Katalog',
            self::POST    => 'Blog',
        };
    }
}
