<?php

namespace App\Enums;

/**
 * CategoryType Enum
 *
 * Discriminator specifying whether a category belongs to catalog items or blog posts.
 * Utilizes the Single Table pattern to eliminate schema duplication.
 */
enum CategoryType: string
{
    /** Category used to group catalog items (products, services, etc.). */
    case CATALOG = 'catalog';

    /** Category used to group blog posts. */
    case POST = 'post';

    /**
     * Return human-readable label in Dutch for UI display.
     */
    public function label(): string
    {
        return match($this) {
            self::CATALOG => 'Catalogus',
            self::POST    => 'Blog',
        };
    }
}
