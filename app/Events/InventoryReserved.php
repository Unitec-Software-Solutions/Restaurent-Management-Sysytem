<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryReserved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $reservedItems;
    public $reservationExpiry;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, array $reservedItems, $reservationExpiry = null)
    {
        $this->order = $order;
        $this->reservedItems = $reservedItems;
        $this->reservationExpiry = $reservationExpiry ?? now()->addMinutes(15); // 15 minute default reservation
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inventory'),
            new PrivateChannel('branch.' . $this->order->branch_id),
            new PrivateChannel('organization.' . $this->order->organization_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'reserved_items' => $this->reservedItems,
            'expiry' => $this->reservationExpiry,
            'branch_id' => $this->order->branch_id,
            'organization_id' => $this->order->organization_id,
        ];
    }
}
