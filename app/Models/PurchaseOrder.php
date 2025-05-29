<?php 

namespace App\Models;

use App\Models\Branch;
use App\Models\Organizations;
use App\Models\Supplier; // Added missing import
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'po_master';
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'branch_id',
        'organization_id',
        'supplier_id',
        'manual_supplier_name',
        'user_id',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_amount',
        'paid_amount',
        'payment_method',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relationships
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    public function grns()
    {
        return $this->hasMany(GrnMaster::class, 'po_id');
    }

    // Helper methods
    public function getBalanceAmount()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isPending()
    {
        return $this->status === 'Pending';
    }

    public function markAsApproved()
    {
        $this->update(['status' => 'Approved']);
    }

    public function markAsReceived()
    {
        $this->update(['status' => 'Received']);
    }
}