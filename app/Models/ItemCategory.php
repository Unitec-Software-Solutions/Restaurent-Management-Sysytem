<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemCategory extends Model
{
    use HasFactory;

    protected $table = 'item_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'organization_id',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope: Active Categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
