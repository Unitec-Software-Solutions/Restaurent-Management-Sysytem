<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id', 
        'quantity',
        'unit_price',
        'total_price',
         'inventory_item_id',
        'order_id',
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
        return $this->belongsTo(\App\Models\MenuItem::class, 'menu_item_id');
        return $this->belongsTo(\App\Models\ItemMaster::class, 'menu_item_id');
    }


    public function inventoryItem()
    {
        return $this->belongsTo(\App\Models\ItemMaster::class, 'inventory_item_id');
    }


}
