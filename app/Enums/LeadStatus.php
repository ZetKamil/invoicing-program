<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum LeadStatus: string implements HasLabel, HasColor
{
    case NEW = 'new';
    case AUDITED = 'audited';
    case CONTACTED = 'contacted';
    case CONVERTED = 'converted';
    case REJECTED = 'rejected';

    public function getLabel(): ?string
    {
        return match($this) {
            self::NEW => 'Nieuw',
            self::AUDITED => 'Gecontroleerd',
            self::CONTACTED => 'Gecontacteerd',
            self::CONVERTED => 'Omgezet',
            self::REJECTED => 'Afgewezen',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::NEW => 'info',
            self::AUDITED => 'warning',
            self::CONTACTED => 'primary',
            self::CONVERTED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
