<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionRequestMaster extends Model
{
    use HasFactory;

    protected $table = 'production_requests_master';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'request_date',
        'required_date',
        'status',
        'notes',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at',
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function items()
    {
        return $this->hasMany(ProductionRequestItem::class, 'production_request_master_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'production_requests_master_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SUBMITTED]);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    // Helper methods
    public function canBeSubmitted()
    {
        return $this->status === self::STATUS_DRAFT && $this->items()->count() > 0;
    }

    public function canBeApproved()
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function canBeCancelled()
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getTotalItemsCount()
    {
        return $this->items()->count();
    }

    public function getTotalQuantityRequested()
    {
        return $this->items()->sum('quantity_requested');
    }

    public function getTotalQuantityApproved()
    {
        return $this->items()->sum('quantity_approved');
    }

    public function getTotalQuantityProduced()
    {
        return $this->items()->sum('quantity_produced');
    }

    public function getProductionProgress()
    {
        $totalApproved = $this->getTotalQuantityApproved();
        $totalProduced = $this->getTotalQuantityProduced();

        return $totalApproved > 0 ? ($totalProduced / $totalApproved) * 100 : 0;
    }

    public function isFullyProduced()
    {
        return $this->items->every(function ($item) {
            return $item->quantity_produced >= $item->quantity_approved;
        });
    }

    public function isFullyDistributed()
    {
        return $this->items->every(function ($item) {
            return $item->quantity_distributed >= $item->quantity_produced;
        });
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_SUBMITTED => 'bg-blue-100 text-blue-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_IN_PRODUCTION => 'bg-yellow-100 text-yellow-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
