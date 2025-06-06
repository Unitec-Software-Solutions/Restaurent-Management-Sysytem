<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\Organizations;
use App\Models\Supplier; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    //  status constants
    const STATUS_PENDING = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_RECEIVED = 'Received';
    const STATUS_PARTIAL = 'Partial';
    const STATUS_CANCELLED = 'Cancelled';

    protected $table = 'po_master';
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'branch_id',
        'organization_id',
        'supplier_id',
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

    protected $dates = [
    'created_at',
    'updated_at'
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organizations::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    public function grns()
    {
        return $this->hasMany(GrnMaster::class, 'po_id');
    }
    public function grnItems()
    {
        return $this->hasManyThrough(
            GrnItem::class,
            GrnMaster::class,
            'po_id', // Foreign key on grn_master table
            'grn_id', // Foreign key on grn_items table
            'po_id', // Local key on po_master table
            'grn_id' // Local key on grn_master table
        );
    }

    // Helper methods
    public function getBalanceAmount()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }
    public function approvedBy()
{
    return $this->belongsTo(User::class, 'approved_by');
}

public function isApproved(): bool
{
    return $this->status === self::STATUS_APPROVED;
}

    public function markAsApproved(): void
    {
        $this->update(['status' => 'Approved']);
    }

    public function markAsReceived()
    {
        $this->update(['status' => 'Received']);
    }

    // Add this to automatically set PO number
protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        $model->po_number = static::generatePONumber();
    });
}

public static function generatePONumber()
{
    // Example: PO-2023-0001
    $latest = static::latest('po_id')->first();
    $nextId = $latest ? $latest->po_id + 1 : 1;
    return 'PO-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}
}
