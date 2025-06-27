<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StockReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'status',
        'reserved_at',
        'expires_at'
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Status constants
    const STATUS_RESERVED = 'reserved';
    const STATUS_COMMITTED = 'committed';
    const STATUS_RELEASED = 'released';

    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Static methods for stock management
     */
    public static function createReservation($menuItemId, $orderId, $quantity, $expiresInMinutes = 30)
    {
        return self::create([
            'order_id' => $orderId,
            'menu_item_id' => $menuItemId,
            'quantity' => $quantity,
            'status' => self::STATUS_RESERVED,
            'reserved_at' => now(),
            'expires_at' => now()->addMinutes($expiresInMinutes)
        ]);
    }

    public function commit()
    {
        $this->update([
            'status' => self::STATUS_COMMITTED,
            'expires_at' => null
        ]);
        
        // Deduct stock from menu item
        $this->menuItem->decrement('stock', $this->quantity);
    }

    public function release()
    {
        $this->update(['status' => self::STATUS_RELEASED]);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->where('status', self::STATUS_RESERVED);
    }

    /**
     * Clean up expired reservations
     */
    public static function releaseExpiredReservations()
    {
        $expired = self::expired()->get();
        
        foreach ($expired as $reservation) {
            $reservation->release();
        }
        
        return $expired->count();
    }
}
