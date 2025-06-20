<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kot extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_SERVED = 'served';
    const STATUS_CANCELLED = 'cancelled';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'order_id',
        'kot_number',
        'station_id',
        'status',
        'priority',
        'estimated_prep_time',
        'actual_prep_time',
        'started_at',
        'completed_at',
        'special_instructions',
        'assigned_chef_id',
        'is_printed',
        'print_count'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_printed' => 'boolean',
        'estimated_prep_time' => 'integer',
        'actual_prep_time' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kot) {
            if (!$kot->kot_number) {
                $kot->kot_number = 'KOT-' . str_pad($kot->order_id, 6, '0', STR_PAD_LEFT);
            }
            
            // Auto-set priority based on order type
            if (!$kot->priority) {
                $kot->priority = $kot->determinePriority();
            }
        });
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function station()
    {
        return $this->belongsTo(KitchenStation::class, 'station_id');
    }

    public function assignedChef()
    {
        return $this->belongsTo(Employee::class, 'assigned_chef_id');
    }

    public function kotItems()
    {
        return $this->hasMany(KotItem::class);
    }

    // Status management
    public function markAsPreparing($chefId = null)
    {
        $this->update([
            'status' => self::STATUS_PREPARING,
            'started_at' => now(),
            'assigned_chef_id' => $chefId
        ]);
    }

    public function markAsReady()
    {
        $actualTime = $this->started_at ? now()->diffInMinutes($this->started_at) : null;
        
        $this->update([
            'status' => self::STATUS_READY,
            'completed_at' => now(),
            'actual_prep_time' => $actualTime
        ]);
    }

    public function markAsServed()
    {
        $this->update(['status' => self::STATUS_SERVED]);
    }

    /**
     * Start preparation of the KOT (alias for markAsPreparing)
     */
    public function startPreparation(): void
    {
        $this->markAsPreparing();
    }

    /**
     * Complete the KOT preparation (alias for markAsReady)
     */
    public function complete(): void
    {
        $this->markAsReady();
    }

    // Priority determination
    private function determinePriority()
    {
        if (!$this->order) {
            return self::PRIORITY_NORMAL;
        }

        // Urgent for walk-in demand orders
        if (str_contains($this->order->order_type, 'walk_in_demand')) {
            return self::PRIORITY_URGENT;
        }

        // High for orders with close pickup times
        if ($this->order->order_time && $this->order->order_time->diffInMinutes(now()) < 30) {
            return self::PRIORITY_HIGH;
        }

        return self::PRIORITY_NORMAL;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePreparing($query)
    {
        return $query->where('status', self::STATUS_PREPARING);
    }

    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPreparing()
    {
        return $this->status === self::STATUS_PREPARING;
    }

    public function isReady()
    {
        return $this->status === self::STATUS_READY;
    }

    public function isOverdue()
    {
        if (!$this->estimated_prep_time || $this->isReady() || $this->status === self::STATUS_SERVED) {
            return false;
        }

        $elapsedTime = $this->started_at ? now()->diffInMinutes($this->started_at) : 0;
        return $elapsedTime > $this->estimated_prep_time;
    }

    public function getEstimatedCompletionTime()
    {
        if (!$this->estimated_prep_time) {
            return null;
        }

        $baseTime = $this->started_at ?: $this->created_at;
        return $baseTime->addMinutes($this->estimated_prep_time);
    }
}
