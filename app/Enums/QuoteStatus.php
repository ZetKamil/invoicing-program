<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum QuoteStatus: string implements HasLabel, HasColor
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';
    case CONVERTED = 'converted';

    public function getLabel(): ?string
    {
        return match($this) {
            self::DRAFT => 'Concept',
            self::SENT => 'Verzonden',
            self::ACCEPTED => 'Geaccepteerd',
            self::DECLINED => 'Afgewezen',
            self::EXPIRED => 'Verlopen',
            self::CONVERTED => 'Omgezet',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::DRAFT => 'info',
            self::SENT => 'primary',
            self::ACCEPTED => 'success',
            self::DECLINED => 'danger',
            self::EXPIRED => 'warning',
            self::CONVERTED => 'success',
        };
    }
}
