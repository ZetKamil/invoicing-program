<?php

namespace App\Enums;

/**
 * PostStatus Enum
 *
 * Publication lifecycle status for blog posts.
 */
enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    /**
     * Return human-readable label in Dutch for UI display.
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT     => 'Concept',
            self::PUBLISHED => 'Gepubliceerd',
        };
    }

    /**
     * Return badge color for Filament UI rendering.
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::DRAFT     => 'gray',
            self::PUBLISHED => 'success',
        };
    }
}
