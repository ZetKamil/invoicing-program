<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELED = 'canceled';
    case PAST_DUE = 'past_due';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::CANCELED => 'Canceled',
            self::PAST_DUE => 'Past Due',
        };
    }
}
