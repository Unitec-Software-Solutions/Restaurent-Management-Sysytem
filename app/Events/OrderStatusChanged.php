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

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $previousStatus;
    public $newStatus;
    public $changedBy;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, string $previousStatus, string $newStatus, $changedBy = null, string $reason = null)
    {
        $this->order = $order;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
        $this->reason = $reason;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders'),
            new PrivateChannel('branch.' . $this->order->branch_id),
            new PrivateChannel('organization.' . $this->order->organization_id),
            new Channel('order.' . $this->order->id), // Public channel for guest order tracking
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
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'changed_by' => $this->changedBy ? [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name,
                'role' => $this->changedBy->roles->first()?->name,
            ] : null,
            'reason' => $this->reason,
            'timestamp' => now()->toISOString(),
            'branch_id' => $this->order->branch_id,
            'organization_id' => $this->order->organization_id,
        ];
    }

    /**
     * Get the name of the event to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }
}
