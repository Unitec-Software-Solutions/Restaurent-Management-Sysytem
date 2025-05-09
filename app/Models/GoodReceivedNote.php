<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceivedNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_number',
        'branch_id',
        'purchase_order_id',
        'supplier_id',
        'received_by',
        'checked_by',
        'received_date',
        'received_time',
        'delivery_note_number',
        'supplier_invoice_number',
        'status',
        'total_amount',
        'notes',
        'has_discrepancy',
        'discrepancy_notes',
        'is_active',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function items()
    {
        return $this->hasMany(GoodReceivedNoteItem::class);
    }
}
