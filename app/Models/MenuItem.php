<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'menu_category_id',
        'name',
        'description',
        'price',
        'image_path',
        'is_available',
        'requires_preparation',
        'preparation_time',
        'station',
        'is_vegetarian',
        'contains_alcohol',
        'allergens',
        'is_active',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'requires_preparation' => 'boolean',
        'is_vegetarian' => 'boolean',
        'contains_alcohol' => 'boolean',
        'is_active' => 'boolean',
        'allergens' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}