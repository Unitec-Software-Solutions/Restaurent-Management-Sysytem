<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemTransaction extends Model
{
    protected $table = 'item_transactions';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'inventory_item_id',
        'transaction_type',
        'transfer_to_branch_id',
        'receiver_user_id',
        'quantity',
        'received_quantity',
        'damaged_quantity',
        'cost_price',
        'unit_price',
        'source_id',
        'source_type',
        'created_by_user_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'cost_price' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    /*
     * Relationships
     */
    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'inventory_item_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organizations::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /*
     * Scope: Only active transactions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /*
     * Accessor: Net Stock Effect
     */
    public function getNetQuantityAttribute()
    {
        return $this->quantity - $this->damaged_quantity;
    }

    /*
     * Stock on Hand (can also be used as a static helper elsewhere)
     */
    public static function stockOnHand($itemId, $branchId = null)
    {
        $query = self::active()->where('inventory_item_id', $itemId);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->sum('quantity') - $query->sum('damaged_quantity');
    }
}
