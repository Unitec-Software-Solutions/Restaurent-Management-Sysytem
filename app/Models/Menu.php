<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'branch_id',
        'name',
        'description',
        'price',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'available_days' => 'array',
        'start_time' => 'string',
        'end_time' => 'string',
        'is_active' => 'boolean',
        'days_of_week' => 'array',
        'activation_time' => 'string',
        'deactivation_time' => 'string',
        'auto_activate' => 'boolean',
    ];

    protected $dates = [
        'date_from',
        'date_to',
        'valid_from',
        'valid_until',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Menu types
    const TYPE_REGULAR = 'regular';
    const TYPE_SPECIAL = 'special';
    const TYPE_SEASONAL = 'seasonal';
    const TYPE_PROMOTIONAL = 'promotional';

    // Days of week mapping
    const DAYS_OF_WEEK = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday'
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_menu_items')
                    ->withPivot([
                        'override_price', 'is_available', 'sort_order',
                        'special_notes', 'available_from', 'available_until'
                    ])
                    ->withTimestamps()
                    ->orderBy('pivot_sort_order');
    }

    public function availableMenuItems()
    {
        return $this->menuItems()->wherePivot('is_available', true);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForDate($query, $date = null)
    {
        $date = $date ?: Carbon::now();
        
        return $query->where(function($q) use ($date) {
            $q->where('date_from', '<=', $date)
              ->where(function($sq) use ($date) {
                  $sq->where('date_to', '>=', $date)
                     ->orWhereNull('date_to');
              });
        });
    }

    public function scopeForDay($query, $dayOfWeek = null)
    {
        $dayOfWeek = $dayOfWeek ?: Carbon::now()->dayOfWeek;
        
        return $query->whereJsonContains('days_of_week', $dayOfWeek);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('menu_type', $type);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeAutoActivatable($query)
    {
        return $query->where('auto_activate', true);
    }

    /**
     * Check if menu is valid for current date/time
     */
    public function isValidForDate($date = null): bool
    {
        $date = $date ?: Carbon::now();
        
        // Check date range
        if ($this->date_from && $date->lt($this->date_from)) {
            return false;
        }
        
        if ($this->date_to && $date->gt($this->date_to)) {
            return false;
        }
        
        // Check day of week using available_days field
        if (!empty($this->available_days) && is_array($this->available_days)) {
            $dayName = strtolower($date->format('l'));
            if (!in_array($dayName, $this->available_days)) {
                return false;
            }
        }
        
        // Also check old days_of_week field for backward compatibility
        if (!empty($this->days_of_week) && is_array($this->days_of_week) && !in_array($date->dayOfWeek, $this->days_of_week)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if menu should be active at current time
     */
    public function shouldBeActiveNow(): bool
    {
        $now = Carbon::now();
        
        // Check if valid for today
        if (!$this->isValidForDate($now)) {
            return false;
        }
        
        // Check time windows if set
        if ($this->activation_time || $this->deactivation_time) {
            $currentTime = $now->format('H:i');
            
            if ($this->activation_time && $currentTime < $this->activation_time) {
                return false;
            }
            
            if ($this->deactivation_time && $currentTime > $this->deactivation_time) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Activate this menu and deactivate others in same branch
     */
    public function activate(): bool
    {
        if (!$this->shouldBeActiveNow()) {
            return false;
        }

        DB::transaction(function() {
            // Deactivate other menus in same branch
            static::where('branch_id', $this->branch_id)
                  ->where('id', '!=', $this->id)
                  ->update(['is_active' => false]);
            
            // Activate this menu
            $this->update(['is_active' => true]);
        });

        return true;
    }

    /**
     * Deactivate this menu
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Get menu summary with item counts
     */
    public function getSummaryAttribute(): array
    {
        return [
            'total_items' => $this->menuItems()->count(),
            'available_items' => $this->availableMenuItems()->count(),
            'categories' => $this->menuItems()
                                ->join('menu_categories', 'menu_items.menu_category_id', '=', 'menu_categories.id')
                                ->distinct('menu_categories.id')
                                ->count(),
            'price_range' => [
                'min' => $this->menuItems()->min('price'),
                'max' => $this->menuItems()->max('price')
            ]
        ];
    }

    /**
     * Get human readable days of week
     */
    public function getDaysOfWeekTextAttribute(): string
    {
        if (empty($this->days_of_week)) {
            return 'All days';
        }
        
        $days = array_map(function($day) {
            return self::DAYS_OF_WEEK[$day] ?? '';
        }, $this->days_of_week);
        
        return implode(', ', array_filter($days));
    }

    /**
     * Get status badge info
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_active) {
            return [
                'text' => 'Active',
                'class' => 'bg-green-100 text-green-800'
            ];
        }
        
        if ($this->shouldBeActiveNow()) {
            return [
                'text' => 'Ready',
                'class' => 'bg-blue-100 text-blue-800'
            ];
        }
        
        if (!$this->isValidForDate()) {
            return [
                'text' => 'Expired',
                'class' => 'bg-red-100 text-red-800'
            ];
        }
        
        return [
            'text' => 'Inactive',
            'class' => 'bg-gray-100 text-gray-800'
        ];
    }

    /**
     * Get available menu items with stock check
     */
    public function getAvailableItemsWithStock(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->availableMenuItems()->get()->filter(function($item) {
            return $item->isAvailableForOrdering();
        });
    }

    /**
     * Static method to get currently active menu for branch
     */
    public static function getActiveMenuForBranch($branchId): ?self
    {
        return static::where('branch_id', $branchId)
                    ->active()
                    ->forDate()
                    ->first();
    }

    /**
     * Static method to get next scheduled menu for branch
     */
    public static function getNextScheduledMenu($branchId): ?self
    {
        $tomorrow = Carbon::tomorrow();
        
        return static::where('branch_id', $branchId)
                    ->forDate($tomorrow)
                    ->autoActivatable()
                    ->orderBy('priority', 'desc')
                    ->first();
    }
}
