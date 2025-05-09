<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'img',
        'is_available',
        'category_id',
        'inventory_item_id',
        'timeslots_availability',
    ];

    // Relationship to Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship to InventoryItem
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function foodItems()
    {
        return $this->belongsToMany(FoodItem::class);
    }

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class);
    }
}
