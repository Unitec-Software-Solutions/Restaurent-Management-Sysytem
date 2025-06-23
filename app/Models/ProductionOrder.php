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
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class, 'production_order_id');
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
    public function canBeApproved()
    {
        return $this->status === self::STATUS_DRAFT && $this->items()->count() > 0;
    }

    public function canBeStarted()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeCompleted()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeCancelled()
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getTotalItemsCount()
    {
        return $this->items()->count();
    }

    public function getTotalQuantityToProduce()
    {
        return $this->items()->sum('quantity_to_produce');
    }

    public function getTotalQuantityProduced()
    {
        return $this->items()->sum('quantity_produced');
    }

    public function getProductionProgress()
    {
        $totalToProduce = $this->getTotalQuantityToProduce();
        $totalProduced = $this->getTotalQuantityProduced();

        return $totalToProduce > 0 ? ($totalProduced / $totalToProduce) * 100 : 0;
    }

    public function isFullyProduced()
    {
        return $this->items->every(function ($item) {
            return $item->quantity_produced >= $item->quantity_to_produce;
        });
    }

    public function getActiveSessions()
    {
        return $this->sessions()->whereIn('status', ['scheduled', 'in_progress'])->get();
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

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_COMPLETED => 'bg-blue-100 text-blue-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
