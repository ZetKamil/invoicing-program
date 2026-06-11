<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum InvoiceStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case VOID = 'void';

    public function getLabel(): ?string
    {
        return match($this) {
            self::DRAFT => 'Concept',
            self::SENT => 'Verzonden',
            self::PAID => 'Betaald',
            self::OVERDUE => 'Vervallen',
            self::VOID => 'Geannuleerd',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::DRAFT => 'info',
            self::SENT => 'primary',
            self::PAID => 'success',
            self::OVERDUE => 'danger',
            self::VOID => 'warning',
        };
    }
}
