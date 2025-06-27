<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'branch_id',
        'organization_id',
        'is_active',
        'staff_required',
        'date'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'date' => 'date',
        'is_active' => 'boolean',
        'staff_required' => 'integer'
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

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Helper methods
     */
    public function isCurrentShift(): bool
    {
        $now = Carbon::now();
        return $now->between($this->start_time, $this->end_time);
    }

    public function getDurationAttribute(): int
    {
        return $this->start_time->diffInHours($this->end_time);
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time->format('g:i A');
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time->format('g:i A');
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return 'bg-gray-100 text-gray-800';
        }

        if ($this->isCurrentShift()) {
            return 'bg-green-100 text-green-800';
        }

        return 'bg-blue-100 text-blue-800';
    }
}
