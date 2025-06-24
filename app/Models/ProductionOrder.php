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

    public function getTotalItemsCount()
    {
        return $this->items()->count();
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
     * Get status badge CSS class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'in_production', 'in_progress' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get production progress percentage
     */
    public function getProductionProgress()
    {
        $totalOrdered = $this->getTotalQuantityOrdered();
        if ($totalOrdered == 0) return 0;

        $totalProduced = $this->getTotalQuantityProduced();
        return ($totalProduced / $totalOrdered) * 100;
    }

    /**
     * Check if production can be started
     */
    public function canStartProduction()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if order can be approved
     */
    public function canBeApproved()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'approved']);
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
        // Calculate based on recipes if available
        $totalTime = 0;
        foreach ($this->items as $item) {
            $recipe = Recipe::where('production_item_id', $item->item_id)->first();
            if ($recipe) {
                $multiplier = $item->quantity_to_produce / $recipe->yield_quantity;
                $totalTime += $recipe->total_time * $multiplier;
            }
        }

        return $totalTime;
    }
}
