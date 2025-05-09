<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use App\Models\InventoryStock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $item;
    protected $stock;

    public function __construct(InventoryItem $item, InventoryStock $stock)
    {
        $this->item = $item;
        $this->stock = $stock;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $stockLevel = $this->stock->current_quantity;
        $reorderLevel = $this->item->reorder_level;
        $urgency = $stockLevel <= ($reorderLevel / 2) ? 'Critical' : 'Warning';

        return (new MailMessage)
            ->subject("{$urgency}: Low Stock Alert - {$this->item->name}")
            ->line("Stock item {$this->item->name} has fallen below the reorder level.")
            ->line("Current stock: {$stockLevel} {$this->item->unit_of_measurement}")
            ->line("Reorder level: {$reorderLevel} {$this->item->unit_of_measurement}")
            ->line("Location: {$this->stock->branch->name}")
            ->action('View Inventory', url('/inventory'));
    }

    public function toArray($notifiable)
    {
        $stockLevel = $this->stock->current_quantity;
        $reorderLevel = $this->item->reorder_level;
        
        return [
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'current_quantity' => $stockLevel,
            'reorder_level' => $reorderLevel,
            'unit_of_measurement' => $this->item->unit_of_measurement,
            'branch_id' => $this->stock->branch_id,
            'branch_name' => $this->stock->branch->name,
            'status' => $stockLevel <= ($reorderLevel / 2) ? 'critical' : 'warning'
        ];
    }
}