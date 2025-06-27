<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Shift;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'employee_id',
        'name',
        'role',
        'branch_id',
        'organization_id',
        'is_available',
        'hourly_rate',
        'shift_preference',
        'performance_rating',
        'current_workload'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'performance_rating' => 'integer',
        'current_workload' => 'integer'
    ];

    /**
     * Relationships
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Helper methods
     */
    public function isAvailable(): bool
    {
        return $this->is_available && $this->current_workload < 5;
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . ' (' . $this->employee_id . ')';
    }
}
