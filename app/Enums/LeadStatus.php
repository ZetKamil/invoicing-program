<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case AUDITED = 'audited';
    case CONTACTED = 'contacted';
    case CONVERTED = 'converted';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::AUDITED => 'Audited',
            self::CONTACTED => 'Contacted',
            self::CONVERTED => 'Converted',
        };
    }
}
