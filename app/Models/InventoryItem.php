<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'item_master_id',
        'supplier_id',
        'batch_number',
        'current_stock',
        'reorder_level',
        'max_stock',
        'cost_price',
        'selling_price',
        'expiry_date',
        'location',
        'status',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function itemMaster()
    {
        return $this->belongsTo(ItemMaster::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'reorder_level');
    }

    /**
     * Accessors
     */
    public function getIsLowStockAttribute()
    {
        return $this->current_stock <= $this->reorder_level;
    }

    public function getTotalValueAttribute()
    {
        return $this->current_stock * $this->cost_price;
    }

    public function getNameAttribute()
    {
        return $this->itemMaster ? $this->itemMaster->name : '';
    }

    public function getUnitAttribute()
    {
        return $this->itemMaster ? $this->itemMaster->unit_of_measurement : '';
    }
}
