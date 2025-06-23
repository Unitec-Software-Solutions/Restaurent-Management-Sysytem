<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MenuCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_inactive',
        'display_order',
        'is_active',
        'branch_id',
        'organization_id',
        'image_path',
        'availability_schedule',
        'is_seasonal',
        'season_start',
        'season_end',
    ];

    protected $casts = [
        'is_inactive' => 'boolean',
        'is_active' => 'boolean',
        'is_seasonal' => 'boolean',
        'availability_schedule' => 'array',
        'season_start' => 'date',
        'season_end' => 'date',
    ];

    /**
     * Category belongs to a branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Category belongs to an organization
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Category has many menu items
     */
    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Get active menu items with stock check
     */
    public function activeMenuItems(int $branchId = null): HasMany
    {
        $branchId = $branchId ?? auth()->user()->branch_id ?? 1;
        
        return $this->menuItems()
            ->active()
            ->available()
            ->withCurrentStock($branchId);
    }

    /**
     * Check if category is currently available based on time and season
     */
    public function isCurrentlyAvailable(): bool
    {
        if (!$this->is_active) return false;

        // Check seasonal availability
        if ($this->is_seasonal) {
            $now = Carbon::now();
            if ($this->season_start && $this->season_end) {
                if ($now->lt($this->season_start) || $now->gt($this->season_end)) {
                    return false;
                }
            }
        }

        // Check time-based availability
        if ($this->availability_schedule) {
            $currentTime = Carbon::now()->format('H:i');
            $currentDay = strtolower(Carbon::now()->format('l'));
            
            $schedule = $this->availability_schedule;
            
            if (isset($schedule[$currentDay])) {
                $daySchedule = $schedule[$currentDay];
                if ($daySchedule['enabled'] ?? false) {
                    $startTime = $daySchedule['start'] ?? '00:00';
                    $endTime = $daySchedule['end'] ?? '23:59';
                    
                    return $currentTime >= $startTime && $currentTime <= $endTime;
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Get category availability status
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if (!$this->is_active) return 'inactive';
        if (!$this->isCurrentlyAvailable()) return 'unavailable';
        
        $availableItems = $this->activeMenuItems()->count();
        if ($availableItems === 0) return 'no_items';
        
        return 'available';
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordered categories
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    // Scope for currently available categories
    public function scopeCurrentlyAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->where('is_seasonal', false)
                  ->orWhere(function($q) {
                      $now = Carbon::now();
                      $q->where('is_seasonal', true)
                        ->where('season_start', '<=', $now)
                        ->where('season_end', '>=', $now);
                  });
            });
    }

    // Scope for branch-specific categories
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // Scope for organization-specific categories
    public function scopeForOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // Scope for seasonal categories that should be active now
    public function scopeSeasonal($query)
    {
        $now = Carbon::now();
        
        return $query->where(function($q) use ($now) {
            // Avurudu (April)
            $q->when($now->month === 4, function($q) {
                $q->orWhere('name', 'Avurudu Special');
            })
            // Christmas (December)
            ->when($now->month === 12, function($q) {
                $q->orWhere('name', 'Christmas Feast');
            })
            // Mango Season (May-July)
            ->when($now->month >= 5 && $now->month <= 7, function($q) {
                $q->orWhere('name', 'Mango Season');
            })
            // Monsoon Season (May-September for SW monsoon, Oct-Jan for NE monsoon)
            ->when(($now->month >= 5 && $now->month <= 9) || 
                  ($now->month >= 10 || $now->month <= 1), function($q) {
                $q->orWhere('name', 'Monsoon Warmers');
            });
        });
    }

    // Scope for current meal period categories
    public function scopeCurrentMealPeriod($query)
    {
        $hour = Carbon::now()->hour;
        
        return $query->where(function($q) use ($hour) {
            // Breakfast (6am-11am)
            $q->when($hour >= 6 && $hour < 11, function($q) {
                $q->orWhere('name', 'Breakfast');
            })
            // Brunch (11am-2pm)
            ->when($hour >= 11 && $hour < 14, function($q) {
                $q->orWhere('name', 'Brunch');
            })
            // Lunch (11am-3pm)
            ->when($hour >= 11 && $hour < 15, function($q) {
                $q->orWhere('name', 'Lunch');
            })
            // Dinner (6pm-11pm)
            ->when($hour >= 18 || $hour < 23, function($q) {
                $q->orWhere('name', 'Dinner');
            });
        });
    }

    /**
     * Get the next available time for this category
     */
    public function getNextAvailableTime(): ?Carbon
    {
        if (!$this->availability_schedule) return null;

        $now = Carbon::now();
        
        // Check today first
        $today = strtolower($now->format('l'));
        if (isset($this->availability_schedule[$today])) {
            $todaySchedule = $this->availability_schedule[$today];
            if ($todaySchedule['enabled'] ?? false) {
                $startTime = Carbon::createFromFormat('H:i', $todaySchedule['start']);
                if ($now->lt($startTime)) {
                    return $startTime;
                }
            }
        }

        // Check next 7 days
        for ($i = 1; $i <= 7; $i++) {
            $checkDate = $now->copy()->addDays($i);
            $dayName = strtolower($checkDate->format('l'));
            
            if (isset($this->availability_schedule[$dayName])) {
                $daySchedule = $this->availability_schedule[$dayName];
                if ($daySchedule['enabled'] ?? false) {
                    return $checkDate->setTimeFromTimeString($daySchedule['start']);
                }
            }
        }

        return null;
    }
}