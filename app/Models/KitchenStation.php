<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenStation extends Model
{

    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code', // REQUIRED FIELD
        'type',
        'description',
        'order_priority',
        'max_capacity',
        'is_active',
        'printer_config',
        'settings',
        'notes'
    ];

    protected $casts = [
        'printer_config' => 'array',
        'settings' => 'array',
        'max_capacity' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the branch that owns the kitchen station
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get UI color class for status badge
     */
    public function getStatusColorAttribute(): string
    {
        if (!$this->is_active) {
            return 'bg-red-100 text-red-800'; // Danger - offline
        }

        $settings = $this->settings ?? [];

        return match($settings['ui_state'] ?? 'default') {
            'active' => 'bg-indigo-100 text-indigo-800', // Primary
            'high-performance' => 'bg-green-100 text-green-800', // Success
            'maintenance' => 'bg-yellow-100 text-yellow-800', // Warning
            'offline' => 'bg-red-100 text-red-800', // Danger
            'premium' => 'bg-purple-100 text-purple-800', // Premium
            'compact' => 'bg-blue-100 text-blue-800', // Info
            default => 'bg-gray-100 text-gray-800' // Default
        };
    }

    /**
     * Get UI icon for dashboard cards
     */
    public function getIconAttribute(): string
    {
        $settings = $this->settings ?? [];
        return $settings['ui_icon'] ?? 'fas fa-utensils';
    }

    /**
     * Get dashboard priority for layout ordering
     */
    public function getDashboardPriorityAttribute(): int
    {
        $settings = $this->settings ?? [];
        return $settings['dashboard_priority'] ?? 5;
    }

    /**
     * Check if station is in maintenance mode
     */
    public function isInMaintenance(): bool
    {
        $settings = $this->settings ?? [];
        return $settings['maintenance_mode'] ?? false;
    }

    /**
     * Check if station supports mobile optimization
     */
    public function isMobileOptimized(): bool
    {
        $settings = $this->settings ?? [];
        return $settings['mobile_optimized'] ?? true;
    }

    /**
     * Scope for active stations only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for stations by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for high priority stations (dashboard ordering)
     */
    public function scopeHighPriority($query)
    {
        return $query->whereJsonContains('settings->dashboard_priority', [1, 2, 3]);
    }
}
