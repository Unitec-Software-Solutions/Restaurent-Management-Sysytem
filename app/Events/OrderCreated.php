<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Phase 2: Order Created Event
 */
class OrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}
}
