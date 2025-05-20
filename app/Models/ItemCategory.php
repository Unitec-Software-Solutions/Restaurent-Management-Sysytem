<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = 'item_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function items()
    {
        return $this->hasMany(ItemMaster::class, 'item_category_id');
    }

    /**
     * Scope: Active Categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
