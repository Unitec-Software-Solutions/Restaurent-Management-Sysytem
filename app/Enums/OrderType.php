<?php

namespace App\Enums;

enum OrderType: string
{
    // Takeaway Order Types
    case TAKEAWAY_IN_CALL_SCHEDULED = 'takeaway_in_call_scheduled';
    case TAKEAWAY_ONLINE_SCHEDULED = 'takeaway_online_scheduled';
    case TAKEAWAY_WALK_IN_SCHEDULED = 'takeaway_walk_in_scheduled';
    case TAKEAWAY_WALK_IN_DEMAND = 'takeaway_walk_in_demand';
    
    // Dine-in Order Types
    case DINE_IN_ONLINE_SCHEDULED = 'dine_in_online_scheduled';
    case DINE_IN_IN_CALL_SCHEDULED = 'dine_in_in_call_scheduled';
    case DINE_IN_WALK_IN_SCHEDULED = 'dine_in_walk_in_scheduled';
    case DINE_IN_WALK_IN_DEMAND = 'dine_in_walk_in_demand';

    /**
     * Get all takeaway types
     */
    public static function takeawayTypes(): array
    {
        return [
            self::TAKEAWAY_IN_CALL_SCHEDULED,
            self::TAKEAWAY_ONLINE_SCHEDULED,
            self::TAKEAWAY_WALK_IN_SCHEDULED,
            self::TAKEAWAY_WALK_IN_DEMAND,
        ];
    }

    /**
     * Get all dine-in types
     */
    public static function dineInTypes(): array
    {
        return [
            self::DINE_IN_ONLINE_SCHEDULED,
            self::DINE_IN_IN_CALL_SCHEDULED,
            self::DINE_IN_WALK_IN_SCHEDULED,
            self::DINE_IN_WALK_IN_DEMAND,
        ];
    }

    /**
     * Check if this is a takeaway order
     */
    public function isTakeaway(): bool
    {
        return in_array($this, self::takeawayTypes());
    }

    /**
     * Check if this is a dine-in order
     */
    public function isDineIn(): bool
    {
        return in_array($this, self::dineInTypes());
    }

    /**
     * Check if this is a scheduled order
     */
    public function isScheduled(): bool
    {
        return str_contains($this->value, 'scheduled');
    }

    /**
     * Check if this is an on-demand order
     */
    public function isOnDemand(): bool
    {
        return str_contains($this->value, 'demand');
    }

    /**
     * Check if this is an online order
     */
    public function isOnline(): bool
    {
        return str_contains($this->value, 'online');
    }

    /**
     * Check if this is a phone/call order
     */
    public function isPhoneOrder(): bool
    {
        return str_contains($this->value, 'in_call');
    }

    /**
     * Check if this is a walk-in order
     */
    public function isWalkIn(): bool
    {
        return str_contains($this->value, 'walk_in');
    }

    /**
     * Get display label
     */
    public function getLabel(): string
    {
        return match($this) {
            self::TAKEAWAY_IN_CALL_SCHEDULED => 'Takeaway - Phone (Scheduled)',
            self::TAKEAWAY_ONLINE_SCHEDULED => 'Takeaway - Online (Scheduled)',
            self::TAKEAWAY_WALK_IN_SCHEDULED => 'Takeaway - Walk-in (Scheduled)',
            self::TAKEAWAY_WALK_IN_DEMAND => 'Takeaway - Walk-in (On Demand)',
            self::DINE_IN_ONLINE_SCHEDULED => 'Dine-in - Online (Scheduled)',
            self::DINE_IN_IN_CALL_SCHEDULED => 'Dine-in - Phone (Scheduled)',
            self::DINE_IN_WALK_IN_SCHEDULED => 'Dine-in - Walk-in (Scheduled)',
            self::DINE_IN_WALK_IN_DEMAND => 'Dine-in - Walk-in (On Demand)',
        };
    }

    /**
     * Get all order types as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get admin default order type
     */
    public static function adminDefault(): self
    {
        return self::DINE_IN_WALK_IN_DEMAND;
    }

    /**
     * Requires reservation validation
     */
    public function requiresReservation(): bool
    {
        return $this->isDineIn();
    }
}
