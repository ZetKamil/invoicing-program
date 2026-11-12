<?php

namespace App\Enums;

enum CommType: string
{
    case EMAIL = 'email';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::SYSTEM => 'System Notification',
        };
    }
}
