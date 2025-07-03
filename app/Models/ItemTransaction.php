<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ItemTransaction extends Model
{
    use SoftDeletes;
    
    protected $table = 'item_transactions';

    /**
     * The attributes that are mass assignable for Laravel + PostgreSQL + Tailwind CSS
     */
    protected $fillable = [
        'organization_id',
        'branch_id',
        'inventory_item_id',
        'item_master_id', 
        'transaction_type',
        'transaction_status',
        'incoming_branch_id',
        'receiver_user_id',
        'quantity',
        'received_quantity',
        'damaged_quantity',
        'waste_quantity',
        'waste_reason',
        'stock_before',
        'stock_after',
        'cost_price',
        'unit_price',
        'total_amount',
        'tax_amount',
        'source_id',
        'source_type',
        'reference_type',
        'reference_id',
        'reference_number',
        'batch_code',
        'expiry_date',
        'quality_status',
        'quality_notes',
        'from_location',
        'to_location',
        'approved_at',
        'approved_by_id',
        'gtn_id',
        'production_session_id',
        'production_order_id',
        'created_by_id',
        'updated_by_id',
        'verified_by',
        'notes',
        'transaction_metadata',
        'is_active',
    ];

    /**
     * The attributes that should be cast for PostgreSQL
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'damaged_quantity' => 'decimal:2',
        'waste_quantity' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'cost_price' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'expiry_date' => 'date',
        'transaction_metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships for Laravel + PostgreSQL + Tailwind CSS
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function incomingBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'incoming_branch_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class, 'inventory_item_id');
    }

    // Dont Remove this Inventory Module items (stock, show ) dashboard breaks without this 
    public function item(): BelongsTo
    {
        return $this->inventoryItem();
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class, 'item_master_id');
    }

    public function receiverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function gtn(): BelongsTo
    {
        return $this->belongsTo(GoodsTransferNote::class, 'gtn_id');
    }

    public function productionSession(): BelongsTo
    {
        return $this->belongsTo(ProductionSession::class);
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * Scopes for PostgreSQL queries optimized for Tailwind CSS filtering
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('transaction_status', $status);
    }

    public function scopeByQualityStatus($query, $status)
    {
        return $query->where('quality_status', $status);
    }

    public function scopeIncoming($query)
    {
        return $query->whereIn('transaction_type', ['stock_in', 'transfer_in', 'purchase', 'production_in']);
    }

    public function scopeOutgoing($query)
    {
        return $query->whereIn('transaction_type', ['stock_out', 'transfer_out', 'sale', 'production_out']);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePending($query)
    {
        return $query->where('transaction_status', 'pending');
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where(function($q) use ($itemId) {
            $q->where('inventory_item_id', $itemId)
              ->orWhere('item_master_id', $itemId);
        });
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Static method to calculate stock on hand for an item at a branch
     * Supports both inventory_item_id and item_master_id for compatibility
     */
    public static function stockOnHand($itemId, $branchId = null): float
    {
        $query = static::query()
            ->where(function($q) use ($itemId) {
                $q->where('inventory_item_id', $itemId)
                  ->orWhere('item_master_id', $itemId);
            })
            ->where('is_active', true);
            
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        // Calculate stock movements
        $stockIn = $query->clone()
            ->whereIn('transaction_type', ['stock_in', 'transfer_in', 'purchase', 'production_in', 'adjustment'])
            ->where('quantity', '>', 0)
            ->sum('quantity');
            
        $stockOut = $query->clone()
            ->whereIn('transaction_type', ['stock_out', 'transfer_out', 'sale', 'production_out', 'adjustment'])
            ->where('quantity', '<', 0)
            ->sum(DB::raw('ABS(quantity)'));
        
        return $stockIn - $stockOut;
    }

    /**
     * Mutators and Accessors for Tailwind CSS UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->transaction_status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            'failed' => 'red',
            default => 'gray'
        };
    }

    public function getQualityColorAttribute()
    {
        return match($this->quality_status) {
            'pending' => 'yellow',
            'passed' => 'green',
            'failed' => 'red',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    public function getTransactionTypeColorAttribute()
    {
        return match($this->transaction_type) {
            'stock_in', 'purchase', 'transfer_in' => 'green',
            'stock_out', 'sale', 'transfer_out' => 'red',
            'adjustment' => 'yellow',
            'production_in', 'production_out' => 'blue',
            default => 'gray'
        };
    }

    public function getFormattedQuantityAttribute()
    {
        $item = $this->inventoryItem ?? $this->itemMaster;
        return number_format($this->quantity, 2) . ' ' . ($item?->unit_of_measurement ?? 'units');
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Auto-calculate total_amount using available price column
            if (empty($transaction->total_amount)) {
                $price = $transaction->cost_price ?? $transaction->unit_price ?? 0;
                $transaction->total_amount = $transaction->quantity * $price;
            }
        });

        static::updating(function ($transaction) {
            // Recalculate total_amount if quantity or price changes
            if ($transaction->isDirty(['quantity', 'cost_price', 'unit_price'])) {
                $price = $transaction->cost_price ?? $transaction->unit_price ?? 0;
                $transaction->total_amount = $transaction->quantity * $price;
            }
        });
    }
}
