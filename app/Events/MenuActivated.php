<?php

namespace App\Events;

use App\Models\Menu;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuActivated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $menu;

    /**
     * Create a new event instance.
     */
    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('branch.' . $this->menu->branch_id),
            new Channel('organization.' . $this->menu->organization_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'menu.activated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menu->id,
            'menu_name' => $this->menu->name,
            'menu_type' => $this->menu->menu_type,
            'branch_id' => $this->menu->branch_id,
            'items_count' => $this->menu->availableMenuItems()->count(),
            'activated_at' => now()->toISOString()
        ];
    }
}
