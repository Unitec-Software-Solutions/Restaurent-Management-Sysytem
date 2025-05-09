<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'cost',
        'ingredients',
        'image_url',
        'prep_time',
        'is_active',
        'portion_size',
        'display_in_menu',
        'available_from',
        'available_to',
        'days_available',
        'promotions',
        'discounts',
    ];

    public function menuCategories()
    {
        return $this->belongsToMany(MenuCategory::class);
    }
}
