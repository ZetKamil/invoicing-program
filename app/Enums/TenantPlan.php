<?php

namespace App\Enums;

/**
 * TenantPlan Enum
 *
 * Definiuje poziomy abonamentu (planu) dostępne dla klientów SaaS.
 * Steruje widocznością modułów w panelu Filament (np. ERP, fakturowanie).
 *
 * Wzorzec Senior: Feature Flag przez Enum zamiast magicznych stringów.
 * Egzaminator: "Plan tenanta jest Enumem PHP 8.1, co daje type-safety i
 * pozwala sterować widocznością zasobów Filament przez canViewAny()."
 */
enum TenantPlan: string
{
    /** Pakiet podstawowy: tylko katalog i blog. ERP wyłączony. */
    case START = 'start';

    /** Pakiet średni: katalog, blog + fakturowanie. */
    case PRO = 'pro';

    /** Pakiet pełny: wszystkie moduły + Peppol UBL + zaawansowane raporty. */
    case SCALE = 'scale';

    /**
     * Zwraca czytelną etykietę planu do wyświetlenia w UI.
     */
    public function label(): string
    {
        return match($this) {
            self::START => 'Start',
            self::PRO   => 'Pro',
            self::SCALE => 'Scale',
        };
    }

    /**
     * Zwraca opis planu — przydatne w panelu SuperAdmina.
     */
    public function description(): string
    {
        return match($this) {
            self::START => 'Katalog produktów + Blog. Brak modułu ERP.',
            self::PRO   => 'Katalog + Blog + Fakturowanie i ofertowanie.',
            self::SCALE => 'Pełny dostęp: ERP, Peppol UBL, zaawansowane raporty.',
        };
    }

    /**
     * Sprawdza czy ten plan ma dostęp do modułu ERP (faktury, oferty).
     * Używane w canViewAny() zasobów Filament.
     */
    public function hasErpAccess(): bool
    {
        return match($this) {
            self::START => false,
            self::PRO, self::SCALE => true,
        };
    }
}
