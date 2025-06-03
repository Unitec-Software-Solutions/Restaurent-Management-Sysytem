<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrnMaster extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'grn_master';
    protected $primaryKey = 'grn_id';

    protected $fillable = [
        'grn_number',
        'po_id',
        'branch_id',
        'organization_id',
        'supplier_id',
        'received_by_user_id',
        'received_date',
        'delivery_note_number',
        'invoice_number',
        'notes',
        'status',
        'is_active',
        'created_by',
        'total_amount'
    ];

    protected $casts = [
        'received_date' => 'date',
        'total_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Payment status constants
    const PAYMENT_STATUS_PENDING = 'Pending';
    const PAYMENT_STATUS_PARTIAL = 'Partial';
    const PAYMENT_STATUS_PAID = 'Paid';
    // Status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_VERIFIED = 'Verified';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_PARTIAL = 'Partially Verified';

    // Relationships



    public function isPaymentPending()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function isPaymentPartial()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIAL;
    }

    public function isPaymentPaid()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function items()
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organizations::class, 'organization_id');
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by_user_id', 'id');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id', 'id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopePartial($query)
    {
        return $query->where('status', self::STATUS_PARTIAL);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('received_date', [$startDate, $endDate]);
    }

    // Status methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isVerified()
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isPartial()
    {
        return $this->status === self::STATUS_PARTIAL;
    }



    // Helper methods
    public function recalculateTotal()
    {
        $this->total_amount = $this->items()->sum('line_total');
        $this->save();
        return $this;
    }

    public function hasPurchaseOrder()
    {
        return !is_null($this->po_id);
    }

    public function getVerificationStatusAttribute()
    {
        if ($this->isVerified()) {
            return 'Verified';
        } elseif ($this->isRejected()) {
            return 'Rejected';
        } elseif ($this->isPartial()) {
            return 'Partially Verified';
        }
        return 'Pending Verification';
    }

    public function calculatePaymentStatus()
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = self::PAYMENT_STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = self::PAYMENT_STATUS_PARTIAL;
        } else {
            $this->payment_status = self::PAYMENT_STATUS_PENDING;
        }
        $this->save();
    }
}
