<?php

namespace App\Enums;

enum ReservationType: string
{
    case ONLINE = 'online';
    case IN_CALL = 'in_call';
    case WALK_IN = 'walk_in';

    /**
     * Get display label
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ONLINE => 'Online Reservation',
            self::IN_CALL => 'Phone Reservation',
            self::WALK_IN => 'Walk-in Reservation',
        };
    }

    /**
     * Get all reservation types as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get default reservation type
     */
    public static function default(): self
    {
        return self::ONLINE;
    }

    /**
     * Check if this reservation type typically has fees
     */
    public function typicallyHasFees(): bool
    {
        return match($this) {
            self::ONLINE => true,
            self::IN_CALL => true,
            self::WALK_IN => false,
        };
    }
}
