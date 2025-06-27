<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'production_requests_master_id',
        'production_order_number',
        'production_date',
        'status',
        'notes',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'production_date' => 'date',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class, 'production_order_id');
    }

    public function ingredients()
    {
        return $this->hasMany(ProductionOrderIngredient::class);
    }

    public function productionRequestMaster()
    {
        return $this->belongsTo(ProductionRequestMaster::class, 'production_requests_master_id');
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

    public function sessions()
    {
        return $this->hasMany(ProductionSession::class, 'production_order_id');
    }

    /**
     * Get the production requests that are part of this order
     */
    public function productionRequests()
    {
        return $this->hasMany(ProductionRequestMaster::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduledForDate($query, $date)
    {
        return $query->whereDate('production_date', $date);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Helper methods
    public function canBeStarted()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeCompleted()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Get status badge CSS class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_IN_PRODUCTION => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-purple-100 text-purple-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get total quantity ordered across all items
     */
    public function getTotalQuantityOrdered()
    {
        return $this->items->sum('quantity_to_produce');
    }

    /**
     * Get total quantity produced across all items
     */
    public function getTotalQuantityProduced()
    {
        return $this->items->sum('quantity_produced');
    }    /**
     * Check if production order is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if production order is in progress
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Get production progress percentage
     */
    public function getProgressPercentage()
    {
        $totalOrdered = $this->getTotalQuantityOrdered();
        if ($totalOrdered == 0) return 0;

        $totalProduced = $this->getTotalQuantityProduced();
        return round(($totalProduced / $totalOrdered) * 100, 2);
    }

    /**
     * Check if order can be approved
     */
    public function canBeApproved()
    {
        return $this->status === self::STATUS_DRAFT && $this->items()->count() > 0;
    }

    /**
     * Check if production can be started
     */
    public function canStartProduction()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Get active production sessions
     */
    public function activeSessions()
    {
        return $this->hasMany(ProductionSession::class)->where('status', 'active');
    }

    public function getCompletedSessions()
    {
        return $this->sessions()->where('status', 'completed')->get();
    }

    public function getEstimatedDuration()
    {
        // Calculate based on production recipes if available
        $totalTime = 0;
        foreach ($this->items as $item) {
            $recipe = ProductionRecipe::where('production_item_id', $item->item_id)->first();
            if ($recipe) {
                $multiplier = $item->quantity_to_produce / $recipe->yield_quantity;
                $totalTime += $recipe->total_time * $multiplier;
            }
        }

        return $totalTime;
    }

    /**
     * Get production progress percentage
     */
    public function getProductionProgress()
    {
        $totalPlanned = $this->items->sum('quantity');
        $totalCompleted = $this->sessions->sum('quantity_produced');

        return $totalPlanned > 0 ? ($totalCompleted / $totalPlanned) * 100 : 0;
    }

    /**
     * Get total quantity of items to be produced
     */
    public function getTotalQuantity()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get total quantity produced across all sessions
     */
    public function getTotalProduced()
    {
        return $this->sessions->sum('quantity_produced');
    }

    /**
     * Check if production order is fully completed
     */
    public function isFullyCompleted()
    {
        return $this->getTotalProduced() >= $this->getTotalQuantity();
    }
}
