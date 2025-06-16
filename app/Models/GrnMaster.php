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
        'total_amount',
        'grand_discount'
    ];

    protected $casts = [
        'received_date' => 'date',
        'total_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    const PAYMENT_STATUS_PENDING = 'Pending';
    const PAYMENT_STATUS_PARTIAL = 'Partial';
    const PAYMENT_STATUS_PAID = 'Paid';
    const STATUS_PENDING = 'Pending';
    const STATUS_VERIFIED = 'Verified';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_PARTIAL = 'Partially Verified';

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

    public function grnItems()
    {
        return $this->hasMany(GrnItem::class, 'grn_id', 'grn_id');
    }

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

    public function getSubTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->accepted_quantity * $item->buying_price;
        });
    }

    public function getItemDiscountTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return ($item->accepted_quantity * $item->buying_price) * ($item->discount_received / 100);
        });
    }

    public function getGrandDiscountAmountAttribute()
    {
        if (!$this->grand_discount) {
            return 0;
        }
        $netAfterItemDiscount = $this->sub_total - $this->item_discount_total;
        return $netAfterItemDiscount * ($this->grand_discount / 100);
    }

    public function getFinalTotalAttribute()
    {
        return $this->sub_total - $this->item_discount_total - $this->grand_discount_amount;
    }

    public function getBalanceAmountAttribute()
    {
        return $this->final_total - ($this->paid_amount ?? 0);
    }

    public function recalculateTotal()
    {
        // Recalculate based on accepted quantities and apply discounts
        $subtotal = $this->items->sum(function ($item) {
            return $item->accepted_quantity * $item->buying_price;
        });

        $itemDiscounts = $this->items->sum(function ($item) {
            return ($item->accepted_quantity * $item->buying_price) * ($item->discount_received / 100);
        });

        $netAfterItemDiscount = $subtotal - $itemDiscounts;
        $grandDiscountAmount = $netAfterItemDiscount * (($this->grand_discount ?? 0) / 100);

        $this->total_amount = $netAfterItemDiscount - $grandDiscountAmount;
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->grn_number)) {
                $model->grn_number = static::generateGRNNumber($model->organization_id);
            }
        });
    }

    public static function generateGRNNumber($organizationId)
    {
        $latest = static::where('organization_id', $organizationId)->latest('grn_id')->first();
        $nextId = $latest ? $latest->grn_id + 1 : 1;
        return 'GRN-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
}
