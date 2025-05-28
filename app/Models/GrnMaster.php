<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrnMaster extends Model
{
    use SoftDeletes;

    protected $table = 'grn_master';
    protected $primaryKey = 'grn_id';

    protected $fillable = [
        'grn_number',
        'po_id',
        'branch_id',
        'organization_id',
        'supplier_id',
        'received_by_user_id',
        'verified_by_user_id',
        'received_date',
        'delivery_note_number',
        'invoice_number',
        'total_amount',
        'status',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'received_date' => 'date',
        'total_amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organizations::class);
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}