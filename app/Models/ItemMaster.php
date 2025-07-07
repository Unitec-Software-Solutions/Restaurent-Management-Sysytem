<?php

namespace App\Models;

use App\Models\Organization;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\ItemCategory;
use App\Models\ItemTransaction;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ItemMaster extends Model
{
    use SoftDeletes, HasFactory;


    protected $table = 'item_master'; 
    

    protected $fillable = [
        'name',
        'unicode_name',
        'description',
        'short_description',
        'item_category_id',
        'organization_id',
        'branch_id',
        'category',
        'subcategory',
        'item_type',
        'item_code',
        'barcode',
        'sku',
        'unit_of_measurement',
        'cost_price',
        'buying_price',
        'selling_price',
        'markup_percentage',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'reorder_level',
        'brand',
        'model',
        'specifications',
        'is_active',
        'is_menu_item',
        'is_inventory_item',
        'is_perishable',
        'track_expiry',
        'requires_production',
        'shelf_life_in_days',
        'primary_supplier_id',
        'supplier_ids',
        'storage_location',
        'storage_requirements',
        'additional_notes',
        'attributes',
        'metadata',
        'created_by',
        'updated_by'
    ];


    protected $casts = [
        'supplier_ids' => 'array',
        'attributes' => 'array',
        'metadata' => 'array',
        'cost_price' => 'decimal:4',
        'buying_price' => 'decimal:4',
        'selling_price' => 'decimal:4',
        'current_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'maximum_stock' => 'decimal:2',
        'reorder_level' => 'integer',
        'markup_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'is_menu_item' => 'boolean',
        'is_inventory_item' => 'boolean',
        'is_perishable' => 'boolean',
        'track_expiry' => 'boolean',
        'requires_production' => 'boolean',
        'shelf_life_in_days' => 'integer'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function transactions()
    {
        return $this->hasMany(ItemTransaction::class, 'inventory_item_id');
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'item_master_id');
    }

    // Dont Remove this Inventory items dashboard breaks without this 
    public function category()
    {
        return $this->itemCategory();
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMenuItems($query)
    {
        return $query->where('is_menu_item', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopePerishable($query)
    {
        return $query->where('is_perishable', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($itemMaster) {
            
            if (empty($itemMaster->item_code)) {
                $itemMaster->item_code = static::generateItemCode($itemMaster);
            }
        });
    }

    private static function generateItemCode($itemMaster)
    {
        $prefix = strtoupper(substr($itemMaster->category ?? 'ITM', 0, 3));
        $lastItem = static::where('item_code', 'like', $prefix . '%')
                         ->orderBy('item_code', 'desc')
                         ->first();
        
        if ($lastItem) {
            $lastNumber = (int) substr($lastItem->item_code, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }


    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }


    public function needsReorder()
    {
        return $this->current_stock <= $this->reorder_level;
    }

    public function getStockStatusAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } elseif ($this->needsReorder()) {
            return 'reorder_needed';
        } else {
            return 'in_stock';
        }
    }

    public function getStockColorAttribute()
    {
        return match($this->stock_status) {
            'out_of_stock' => 'red',
            'low_stock' => 'orange',
            'reorder_needed' => 'yellow',
            'in_stock' => 'green',
            default => 'gray'
        };
    }
}
