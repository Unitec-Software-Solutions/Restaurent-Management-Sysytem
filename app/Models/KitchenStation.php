<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KitchenStation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'station_code',
        'station_type',
        'branch_id',
        'organization_id',
        'location',
        'capacity',
        'priority_order',
        'equipment',
        'is_active',
        'auto_assign_kots',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_assign_kots' => 'boolean',
        'capacity' => 'integer',
        'priority_order' => 'integer',
    ];

    /**
     * Get the branch that owns the kitchen station
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the organization that owns the kitchen station
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the KOTs assigned to this station
     */
    public function kots(): HasMany
    {
        return $this->hasMany(Kot::class);
    }

    /**
     * Get active KOTs for this station
     */
    public function activeKots(): HasMany
    {
        return $this->kots()->whereIn('status', ['pending', 'preparing']);
    }

    /**
     * Get the current workload percentage
     */
    public function getWorkloadPercentageAttribute(): float
    {
        if (!$this->capacity) {
            return 0;
        }
        
        $activeCount = $this->activeKots()->count();
        return min(($activeCount / $this->capacity) * 100, 100);
    }

    /**
     * Check if station is at capacity
     */
    public function isAtCapacity(): bool
    {
        if (!$this->capacity) {
            return false;
        }
        
        return $this->activeKots()->count() >= $this->capacity;
    }

    /**
     * Scope to get only active stations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority_order')->orderBy('name');
    }
}
