<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ItemTransaction extends Model
{
    protected $table = 'item_transactions';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'inventory_item_id',
        'transaction_type',
        'incoming_branch_id',
        'receiver_user_id',
        'quantity', // Can be positive (stock in) or negative (stock out)
        'received_quantity',
        'damaged_quantity',
        'cost_price',
        'unit_price',
        'source_id',
        'source_type',
        'gtn_id', // Reference to GTN
        'created_by_user_id',
        'verified_by', // User who verified the transaction
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
        'source_id' => 'string', // Ensure source_id is always treated as a string
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
        return $this->belongsTo(Organization::class);
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

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function gtn()
    {
        return $this->belongsTo(GoodsTransferNote::class, 'gtn_id', 'gtn_id');
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
        //Log::info('Calculating stock on hand', ['item_id' => $itemId, 'branch_id' => $branchId]);

        $query = self::where('inventory_item_id', $itemId)->where('is_active', true);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $transactions = $query->get();

        $stock = $transactions->sum('quantity');
        //Log::info('Stock on hand calculated', ['item_id' => $itemId, 'branch_id' => $branchId, 'stock' => $stock]);

        return $stock;
    }

    // GTN-specific helper methods
    public static function createGTNOutgoingTransaction($data)
    {
        return self::create(array_merge($data, [
            'transaction_type' => 'gtn_outgoing',
            'quantity' => -abs($data['quantity']), // Ensure negative for outgoing
            'is_active' => true,
        ]));
    }

    public static function createGTNIncomingTransaction($data)
    {
        return self::create(array_merge($data, [
            'transaction_type' => 'gtn_incoming',
            'quantity' => abs($data['quantity']), // Ensure positive for incoming
            'is_active' => true,
        ]));
    }

    public static function createGTNRejectionTransaction($data)
    {
        return self::create(array_merge($data, [
            'transaction_type' => 'gtn_rejection',
            'quantity' => abs($data['quantity']), // Positive for return to sender
            'is_active' => true,
        ]));
    }

    // Scope for GTN transactions
    public function scopeGTNTransactions($query, $gtnId = null)
    {
        $query->whereIn('transaction_type', ['gtn_outgoing', 'gtn_incoming', 'gtn_rejection']);

        if ($gtnId) {
            $query->where('gtn_id', $gtnId);
        }

        return $query;
    }

    // Get stock movements for a specific GTN
    public static function getGTNStockMovements($gtnId)
    {
        return self::where('gtn_id', $gtnId)
                  ->whereIn('transaction_type', ['gtn_outgoing', 'gtn_incoming', 'gtn_rejection'])
                  ->with(['item', 'branch', 'createdBy'])
                  ->orderBy('created_at')
                  ->get();
    }
}
