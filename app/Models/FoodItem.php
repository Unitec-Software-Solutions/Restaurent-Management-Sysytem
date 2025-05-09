<?php

// app/Models/FoodItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodItem extends Model
{
    use SoftDeletes; // Enable soft deletes

    // Specify the table name (optional, if it follows Laravel's naming convention)
    protected $table = 'food_items';

    // Specify the primary key (optional, if it's not 'id')
    protected $primaryKey = 'item_id';

    // Define fillable fields (optional, for mass assignment)
    protected $fillable = [
        'name',
        'price',
        'cost',
        'ingredients',
        'img',
        'is_active',
        'pre_time',
        'portion_size',
        'display_in_menu',
        'available_from',
        'available_to',
        'available_days',
        'promotions',
        'discounts',
    ];

    // Define guarded fields (optional, if you prefer to guard specific fields)
    // protected $guarded = [];

    // Define timestamps (optional, if you don't want timestamps)
    public $timestamps = true;

    // Define date fields (optional, for soft deletes and other date fields)
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
}
