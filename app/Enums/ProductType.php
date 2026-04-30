<?php

namespace App\Enums;

enum ProductType: string
{
    case SERVICE = 'service';
    case LICENSE = 'license';
    case PHYSICAL = 'physical';

    public function label(): string
    {
        return match($this) {
            self::SERVICE => 'Service',
            self::LICENSE => 'License',
            self::PHYSICAL => 'Physical Product',
        };
    }
}
