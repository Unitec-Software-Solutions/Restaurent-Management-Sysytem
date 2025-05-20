<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'menu_item_id', // <-- updated field name
        'quantity',
        'unit_price',
        'total_price',
        // add other fields as needed
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function createOrderItem($order, $item, $inventoryItem)
    {
        return self::create([
            'order_id' => $order->id,
            'menu_item_id' => $item['item_id'], // <-- use menu_item_id as required by your migration
            'quantity' => $item['quantity'],
            'unit_price' => $inventoryItem->selling_price,
            'total_price' => $inventoryItem->selling_price * $item['quantity'],
        ]);
    }
}
