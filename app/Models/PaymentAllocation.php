<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $table = 'payment_allocations';

    protected $fillable = [
        'payment_id',
        'grn_id',
        'po_id',
        'amount',
        'allocated_at',
        'allocated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(SupplierPaymentMaster::class, 'payment_id');
    }

    public function grn()
    {
        return $this->belongsTo(GrnMaster::class, 'grn_id');
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }
}