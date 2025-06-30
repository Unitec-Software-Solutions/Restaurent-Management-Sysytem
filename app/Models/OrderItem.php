<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id', 
        'item_name',
        'quantity',
        'unit_price',
        'total_price',
        'subtotal',
        'inventory_item_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function createOrderItem($order, $item, $inventoryItem)
    {
        return self::create([
            'order_id' => $order->id,
            'menu_item_id' => $item['item_id'], 
            'quantity' => $item['quantity'],
            'unit_price' => $inventoryItem->selling_price,
            'total_price' => $inventoryItem->selling_price * $item['quantity'],
        ]);
    }
    
    public function menuItem()
    {
        return $this->belongsTo(ItemMaster::class, 'menu_item_id');
    }


    public function inventoryItem()
    {
        return $this->belongsTo(\App\Models\ItemMaster::class, 'inventory_item_id');
    }


}
