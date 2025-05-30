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

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'supp_payments_master';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_date' => 'date',
        'total_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
    ];

    /**
     * Payment status enumeration
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REVERSED = 'reversed';

    // Relationships

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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function paymentDetails(): HasMany
    {
        return $this->hasMany(SupplierPaymentDetail::class, 'payment_master_id');
    }

    public function grns()
    {
        return $this->belongsToMany(GrnMaster::class, 'payment_allocations', 'payment_id', 'grn_id')
            ->withPivot('amount', 'allocated_at', 'allocated_by');
    }
    public function allocateToGrn(GrnMaster $grn, $amount)
    {
        DB::beginTransaction();
        try {
            $this->grns()->attach($grn->id, [
                'amount' => $amount,
                'allocated_at' => now(),
                'allocated_by' => Auth::id()
            ]);

            // Recalculate GRN payment status
            $grn->calculatePaymentStatus();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
