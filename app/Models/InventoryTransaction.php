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
        return in_array($this->transaction_type, [
            'purchase',                     // Supplier orders
            'transfer_in',                  // Stock received from another location
            'return',                       // Stock returned (e.g., from kitchen or customer)
            'grn_adjustment',               // Goods Received Note (GRN) adjustment
            'stock_addition',               // Manual stock addition
            'positive_adjustment',          // Adjustment that increases inventory
            'stocktake_positive_variance',  // Stocktake reveals more stock than recorded
            'supplier_stock_return',        // Returning unused stock to supplier (credit)
            'recipe_reversal',              // Reversing a previously recorded recipe usage
            'stock_replenishment',          // replenishment from central kitchen or supplier
            'initial_stock',                // For initial inventory setup
        ]);
    }

    /**
     * Check if transaction is an outgoing transaction
     */
    public function isOutgoingTransaction()
    {
        return in_array($this->transaction_type, [
            'transfer_out',             // Stock sent to another location
            'usage',                    // Stock used in recipes
        //  'spoilage',                 // Food that has spoiled
            'wastage',                  // Spoilage, expired, or damaged stock
            'negative_adjustment',      // Adjustment that reduces inventory
            'stocktake_negative_variance', // Stocktake reveals less stock than recorded
            'donation',                 // Stock donated to charity
            'theft_or_loss',            // Stock lost due to theft or mismanagement
            'supplier_return',          // Returning stock to supplier (physical return)
            'sample_given',             // Stock given as samples or free meals
            'promotional_item',         // Food given away as a promotion
            'employee_meal',            // Food consumed by employees
        ]);
    }

    /**
     * Get formatted transaction type
     */
    public function getFormattedTypeAttribute()
    {
        $types = [
            // Incoming Transactions
            'purchase' => 'Purchase',
            'transfer_in' => 'Transfer In',
            'return' => 'Return',
            'grn_adjustment' => 'GRN Adjustment',
            'stock_addition' => 'Stock Addition',
            'positive_adjustment' => 'Positive Adjustment',
            'stocktake_positive_variance' => 'Stocktake Positive Variance',
            'supplier_stock_return' => 'Supplier Stock Return',
            'recipe_reversal' => 'Recipe Reversal',
            'stock_replenishment' => 'Stock Replenishment',
            'initial_stock' => 'Initial Stock',

            // Outgoing Transactions
            'transfer_out' => 'Transfer Out',
            'usage' => 'Usage',
            'wastage' => 'Wastage',
            'negative_adjustment' => 'Negative Adjustment',
            'stocktake_negative_variance' => 'Stocktake Negative Variance',
            'donation' => 'Donation',
            'theft_or_loss' => 'Theft/Loss',
            'supplier_return' => 'Supplier Return',
            'sample_given' => 'Sample Given',
            'promotional_item' => 'Promotional Item',
            'employee_meal' => 'Employee Meal',
        ];
        
        return $types[$this->transaction_type] ?? $this->transaction_type;
    }

    public function getTypeColor()
{
    return match($this->transaction_type) {
        'purchase' => 'bg-green-100 text-green-800',
        'transfer_in' => 'bg-blue-100 text-blue-800',
        'transfer_out' => 'bg-yellow-100 text-yellow-800',
        'usage' => 'bg-gray-100 text-gray-800',
        'wastage' => 'bg-red-100 text-red-800',
        'return' => 'bg-green-100 text-green-800', // Assuming return is a form of positive inventory change.
        'grn_adjustment' => 'bg-green-100 text-green-800', // Goods received - positive change
        'stock_addition' => 'bg-green-100 text-green-800', //positive inventory change
        'positive_adjustment' => 'bg-green-100 text-green-800', //positive inventory change
        'stocktake_positive_variance' => 'bg-green-100 text-green-800', // positive discovery
        'supplier_stock_return' => 'bg-green-100 text-green-800',   //Supplier gives back positive
        'recipe_reversal' => 'bg-green-100 text-green-800',
        'stock_replenishment' => 'bg-green-100 text-green-800',
        'initial_stock' => 'bg-green-100 text-green-800',
        'negative_adjustment' => 'bg-red-100 text-red-800',         //negative change.
        'stocktake_negative_variance' => 'bg-red-100 text-red-800', // negative discovery
        'donation' => 'bg-red-100 text-red-800',                    //giving away stock.
        'theft_or_loss' => 'bg-red-100 text-red-800',               //stock gone.
        'supplier_return' => 'bg-red-100 text-red-800',             // giving stock back
        'sample_given' => 'bg-red-100 text-red-800',                //giving away stock
        'promotional_item' => 'bg-red-100 text-red-800',            //stock given away.
        'employee_meal' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800'
    };
}

} 