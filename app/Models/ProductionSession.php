<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'production_order_id',
        'session_name',
        'start_time',
        'end_time',
        'status',
        'notes',
        'supervisor_user_id'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    // Helper methods
    public function canBeStarted()
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function canBeCompleted()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeCancelled()
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getDuration()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }

        return null;
    }

    public function getFormattedDuration()
    {
        $duration = $this->getDuration();

        if (!$duration) {
            return 'N/A';
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get estimated duration for the session in minutes
     */
    public function getEstimatedDuration()
    {
        return $this->estimated_duration ?? 60; // Default 60 minutes
    }

    /**
     * Get actual duration of the session in minutes
     */
    public function getActualDuration()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        
        return $this->actual_duration ?? null;
    }

    /**
     * Calculate session efficiency percentage
     */
    public function getEfficiencyPercentage()
    {
        $estimated = $this->getEstimatedDuration();
        $actual = $this->getActualDuration();
        
        if (!$actual || $actual == 0) {
            return 0;
        }
        
        return round(($estimated / $actual) * 100, 1);
    }

    /**
     * Check if session is overdue
     */
    public function isOverdue()
    {
        if ($this->status === 'completed') {
            return false;
        }
        
        $expectedEndTime = $this->start_time ? 
            $this->start_time->addMinutes($this->getEstimatedDuration()) : 
            $this->created_at->addMinutes($this->getEstimatedDuration());
            
        return now()->isAfter($expectedEndTime);
    }
}
