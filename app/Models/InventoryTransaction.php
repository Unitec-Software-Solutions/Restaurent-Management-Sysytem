<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'transaction_type',
        'quantity',
        'unit_price',
        'source_id',
        'source_type',
        'user_id',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the branch that owns the transaction.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the inventory item that owns the transaction.
     */
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * Get the user that created the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source model for the transaction.
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Calculate the transaction value
     */
    public function getTransactionValue()
    {
        return $this->quantity * ($this->unit_price ?? 0);
    }

    /**
     * Check if transaction is an incoming transaction
     */
    public function isIncomingTransaction()
    {
        return in_array($this->transaction_type, ['purchase', 'transfer_in', 'adjustment', 'return']);
    }

    /**
     * Check if transaction is an outgoing transaction
     */
    public function isOutgoingTransaction()
    {
        return in_array($this->transaction_type, ['transfer_out', 'usage', 'wastage']);
    }

    /**
     * Get formatted transaction type
     */
    public function getFormattedTypeAttribute()
    {
        $types = [
            'purchase' => 'Purchase',
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'usage' => 'Usage',
            'adjustment' => 'Adjustment',
            'return' => 'Return',
            'wastage' => 'Wastage',
        ];
        
        return $types[$this->transaction_type] ?? $this->transaction_type;
    }
} 