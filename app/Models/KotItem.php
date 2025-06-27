<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KotItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kot_id',
        'order_item_id',
        'menu_item_id',
        'quantity',
        'status',
        'special_instructions',
        'allergen_notes',
        'priority',
        'estimated_prep_time',
        'actual_prep_time',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_prep_time' => 'integer',
        'actual_prep_time' => 'integer'
    ];

    // Relationships
    public function kot()
    {
        return $this->belongsTo(Kot::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }

    // Status management
    public function markAsStarted()
    {
        $this->update([
            'status' => 'preparing',
            'started_at' => now()
        ]);
    }

    public function markAsCompleted()
    {
        $actualTime = $this->started_at ? now()->diffInMinutes($this->started_at) : null;
        
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_prep_time' => $actualTime
        ]);
    }
}
