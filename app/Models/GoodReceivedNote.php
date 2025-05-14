<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodReceivedNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'grn_number',
        'grn_date',
        'branch_id',
        'purchase_order_id',
        'supplier_id',
        'supplier_code',
        'received_by',
        'checked_by',
        'delivery_note_number',
        'supplier_invoice_number',
        'supplier_invoice_no',
        'description',
        'status',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'payable_amount',
        'paid_amount',
        'notes',
        'has_discrepancy',
        'discrepancy_notes',
        'is_active',
        'created_by',
        'ip_address'
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function altSupplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'supplier_code');
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
        return $this->hasMany(GoodReceivedNoteItem::class, 'good_received_note_id');
    }
}
