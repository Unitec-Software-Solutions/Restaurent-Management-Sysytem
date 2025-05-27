<?php

namespace App\Models;

class OrderStatusStateMachine
{
    // Define possible statuses and their valid transitions
    protected static $transitions = [
        'draft' => ['active', 'cancelled'],
        'active' => ['submitted', 'cancelled'],
        'submitted' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Get valid status transitions for a given status
     *
     * @param string $currentStatus
     * @return array
     */
    public static function getValidTransitions($currentStatus)
    {
        return static::$transitions[$currentStatus] ?? [];
    }
}
