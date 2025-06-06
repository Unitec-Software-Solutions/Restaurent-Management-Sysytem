<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierPaymentMaster extends Model
{
    use SoftDeletes;

    protected $table = 'supp_payments_master';

    protected $fillable = [
        'organization_id',
        'po_id',
        'grn_id',
        'supplier_id',
        'branch_id',
        'payment_number',
        'payment_date',
        'total_amount',
        'allocated_amount',
        'currency',
        'payment_status',
        'processed_by',
        'notes'
        
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REVERSED = 'reversed';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organizations::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GrnMaster::class, 'grn_id', 'grn_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function paymentDetails()
    {
        return $this->hasOne(SupplierPaymentDetail::class, 'payment_master_id');
    }

    public function grns()
    {
        return $this->belongsToMany(GrnMaster::class, 'payment_allocations', 'payment_id', 'grn_id')
                    ->withPivot(['amount', 'allocated_at', 'allocated_by']);
    }
    

    public function purchaseOrders()
    {
        return $this->belongsToMany(PurchaseOrder::class, 'payment_allocations', 'payment_id', 'po_id')
            ->withPivot('amount', 'allocated_at', 'allocated_by');
    }

    public function allocateToGrn(GrnMaster $grn, $amount)
    {
        DB::beginTransaction();
        try {
            $this->grns()->attach($grn->grn_id, [
                'amount' => $amount,
                'allocated_at' => now(),
                'allocated_by' => Auth::id()
            ]);

            $grn->calculatePaymentStatus();
            $grn->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function allocateToPurchaseOrder(PurchaseOrder $po, $amount)
    {
        DB::beginTransaction();
        try {
            $this->purchaseOrders()->attach($po->po_id, [
                'amount' => $amount,
                'allocated_at' => now(),
                'allocated_by' => Auth::id()
            ]);

            $po->paid_amount = ($po->paid_amount ?? 0) + $amount;
            if ($po->paid_amount >= $po->total_amount) {
                $po->payment_status = 'Paid';
            } elseif ($po->paid_amount > 0) {
                $po->payment_status = 'Partial';
            } else {
                $po->payment_status = 'Pending';
            }
            $po->save();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id');
    }

}