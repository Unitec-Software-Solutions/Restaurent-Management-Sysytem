<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiryAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $item;

    public function __construct(InventoryItem $item)
    {
        $this->item = $item;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $daysUntilExpiry = Carbon::parse($this->item->expiry_date)->diffInDays(now());
        $urgency = $daysUntilExpiry <= 3 ? 'Critical' : 'Warning';

        return (new MailMessage)
            ->subject("{$urgency}: Expiring Stock Alert - {$this->item->name}")
            ->line("Stock item {$this->item->name} is approaching its expiry date.")
            ->line("Expiry date: " . Carbon::parse($this->item->expiry_date)->format('M d, Y'))
            ->line("Days remaining: {$daysUntilExpiry}")
            ->line("Current stock: " . $this->item->stocks->sum('current_quantity') . " {$this->item->unit_of_measurement}")
            ->action('View Inventory', url('/inventory'));
    }

    public function toArray($notifiable)
    {
        return [
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'expiry_date' => $this->item->expiry_date,
            'days_until_expiry' => Carbon::parse($this->item->expiry_date)->diffInDays(now()),
            'current_quantity' => $this->item->stocks->sum('current_quantity'),
            'unit_of_measurement' => $this->item->unit_of_measurement,
            'status' => Carbon::parse($this->item->expiry_date)->diffInDays(now()) <= 3 ? 'critical' : 'warning'
        ];
    }
}