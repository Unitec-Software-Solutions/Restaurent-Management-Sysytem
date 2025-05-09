<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrderItem;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id',
        'supplier_id',
        'user_id',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_amount',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the supplier that owns the purchase order.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user that created the purchase order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the branch that the purchase order belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the items for the purchase order.
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}