<?php

namespace App\Enums;

/**
 * Backed enum for User Roles in the multi-tenancy system.
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case DISPATCHER = 'dispatcher';

    /**
     * Get the label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::DISPATCHER => 'Dispatcher',
        };
    }
}
