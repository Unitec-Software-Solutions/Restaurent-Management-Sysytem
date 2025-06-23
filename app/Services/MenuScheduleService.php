<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Branch;
use App\Events\MenuActivated;
use App\Events\MenuDeactivated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MenuScheduleService
{
    /**
     * Activate menus that should be active now
     */
    public function activateScheduledMenus(): array
    {
        $results = [];
        $branches = Branch::all();
        
        foreach ($branches as $branch) {
            $result = $this->activateMenuForBranch($branch->id);
            $results[$branch->id] = $result;
        }
        
        return $results;
    }

    /**
     * Activate the appropriate menu for a specific branch
     */
    public function activateMenuForBranch(int $branchId): array
    {
        $currentActiveMenu = Menu::getActiveMenuForBranch($branchId);
        $shouldBeActiveMenu = $this->getMenuThatShouldBeActive($branchId);
        
        $result = [
            'branch_id' => $branchId,
            'previous_menu' => $currentActiveMenu?->name,
            'new_menu' => $shouldBeActiveMenu?->name,
            'action' => 'none',
            'success' => true,
            'message' => 'No changes needed'
        ];
        
        try {
            // If current menu should not be active, deactivate it
            if ($currentActiveMenu && (!$currentActiveMenu->shouldBeActiveNow() || 
                ($shouldBeActiveMenu && $shouldBeActiveMenu->id !== $currentActiveMenu->id))) {
                
                $currentActiveMenu->deactivate();
                event(new MenuDeactivated($currentActiveMenu));
                $result['action'] = 'deactivated';
                $result['message'] = "Deactivated menu: {$currentActiveMenu->name}";
                
                Log::info("Menu deactivated", [
                    'menu_id' => $currentActiveMenu->id,
                    'branch_id' => $branchId,
                    'menu_name' => $currentActiveMenu->name
                ]);
            }
            
            // If there's a menu that should be active and it's not currently active
            if ($shouldBeActiveMenu && (!$currentActiveMenu || $shouldBeActiveMenu->id !== $currentActiveMenu->id)) {
                $shouldBeActiveMenu->activate();
                event(new MenuActivated($shouldBeActiveMenu));
                $result['action'] = $result['action'] === 'deactivated' ? 'switched' : 'activated';
                $result['message'] = "Activated menu: {$shouldBeActiveMenu->name}";
                
                Log::info("Menu activated", [
                    'menu_id' => $shouldBeActiveMenu->id,
                    'branch_id' => $branchId,
                    'menu_name' => $shouldBeActiveMenu->name
                ]);
            }
            
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = "Error: " . $e->getMessage();
            
            Log::error("Menu activation failed", [
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $result;
    }

    /**
     * Get the menu that should be active for a branch at current time
     */
    protected function getMenuThatShouldBeActive(int $branchId): ?Menu
    {
        $now = Carbon::now();
        
        return Menu::where('branch_id', $branchId)
                   ->where('auto_activate', true)
                   ->where('date_from', '<=', $now->toDateString())
                   ->where('date_to', '>=', $now->toDateString())
                   ->where(function($query) use ($now) {
                       $query->whereNull('days_of_week')
                             ->orWhereJsonContains('days_of_week', $now->dayOfWeek);
                   })
                   ->where(function($query) use ($now) {
                       $currentTime = $now->format('H:i:s');
                       $query->where(function($q) use ($currentTime) {
                           $q->whereNull('activation_time')
                             ->orWhere('activation_time', '<=', $currentTime);
                       })->where(function($q) use ($currentTime) {
                           $q->whereNull('deactivation_time')
                             ->orWhere('deactivation_time', '>=', $currentTime);
                       });
                   })
                   ->orderBy('priority', 'desc')
                   ->orderBy('created_at', 'desc')
                   ->first();
    }

    /**
     * Manually activate a specific menu
     */
    public function manuallyActivateMenu(int $menuId): array
    {
        $menu = Menu::findOrFail($menuId);
        
        $result = [
            'menu_id' => $menuId,
            'menu_name' => $menu->name,
            'branch_id' => $menu->branch_id,
            'success' => false,
            'message' => ''
        ];
        
        try {
            if (!$menu->isValidForDate()) {
                $result['message'] = 'Menu is not valid for current date';
                return $result;
            }
            
            $currentActive = Menu::getActiveMenuForBranch($menu->branch_id);
            
            if ($currentActive && $currentActive->id === $menu->id) {
                $result['message'] = 'Menu is already active';
                $result['success'] = true;
                return $result;
            }
            
            if ($menu->activate()) {
                event(new MenuActivated($menu));
                $result['success'] = true;
                $result['message'] = 'Menu activated successfully';
                
                Log::info("Menu manually activated", [
                    'menu_id' => $menuId,
                    'branch_id' => $menu->branch_id,
                    'menu_name' => $menu->name
                ]);
            } else {
                $result['message'] = 'Failed to activate menu';
            }
            
        } catch (\Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
            
            Log::error("Manual menu activation failed", [
                'menu_id' => $menuId,
                'error' => $e->getMessage()
            ]);
        }
        
        return $result;
    }

    /**
     * Get upcoming menu transitions for next 7 days
     */
    public function getUpcomingTransitions(int $branchId, int $days = 7): array
    {
        $transitions = [];
        $startDate = Carbon::now();
        
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayTransitions = $this->getTransitionsForDate($branchId, $date);
            
            if (!empty($dayTransitions)) {
                $transitions[$date->toDateString()] = $dayTransitions;
            }
        }
        
        return $transitions;
    }

    /**
     * Get menu transitions for a specific date
     */
    protected function getTransitionsForDate(int $branchId, Carbon $date): array
    {
        $transitions = [];
        
        $menusForDay = Menu::where('branch_id', $branchId)
                          ->where('auto_activate', true)
                          ->where('date_from', '<=', $date->toDateString())
                          ->where('date_to', '>=', $date->toDateString())
                          ->where(function($query) use ($date) {
                              $query->whereNull('days_of_week')
                                    ->orWhereJsonContains('days_of_week', $date->dayOfWeek);
                          })
                          ->orderBy('activation_time')
                          ->get();
        
        foreach ($menusForDay as $menu) {
            if ($menu->activation_time) {
                $transitions[] = [
                    'time' => $menu->activation_time->format('H:i'),
                    'action' => 'activate',
                    'menu' => $menu->name,
                    'menu_id' => $menu->id
                ];
            }
            
            if ($menu->deactivation_time) {
                $transitions[] = [
                    'time' => $menu->deactivation_time->format('H:i'),
                    'action' => 'deactivate',
                    'menu' => $menu->name,
                    'menu_id' => $menu->id
                ];
            }
        }
        
        // Sort by time
        usort($transitions, function($a, $b) {
            return strcmp($a['time'], $b['time']);
        });
        
        return $transitions;
    }

    /**
     * Create default menus for a branch
     */
    public function createDefaultMenusForBranch(int $branchId, int $organizationId): array
    {
        $defaultMenus = [
            [
                'name' => 'Breakfast Menu',
                'description' => 'Morning breakfast items',
                'menu_type' => 'regular',
                'activation_time' => '06:00:00',
                'deactivation_time' => '11:00:00',
                'priority' => 3
            ],
            [
                'name' => 'Lunch Menu',
                'description' => 'Midday lunch specialties',
                'menu_type' => 'regular',
                'activation_time' => '11:00:00',
                'deactivation_time' => '16:00:00',
                'priority' => 2
            ],
            [
                'name' => 'Dinner Menu',
                'description' => 'Evening dinner selection',
                'menu_type' => 'regular',
                'activation_time' => '16:00:00',
                'deactivation_time' => '23:00:00',
                'priority' => 1
            ]
        ];
        
        $created = [];
        $startDate = Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->addWeeks(4); // 4 weeks ahead
        
        foreach ($defaultMenus as $menuData) {
            $menu = Menu::create(array_merge($menuData, [
                'branch_id' => $branchId,
                'organization_id' => $organizationId,
                'date_from' => $startDate->toDateString(),
                'date_to' => $endDate->toDateString(),
                'days_of_week' => [1, 2, 3, 4, 5, 6, 0], // All days
                'auto_activate' => true,
                'is_active' => false
            ]));
            
            $created[] = $menu;
        }
        
        return $created;
    }

    /**
     * Get menu statistics for dashboard
     */
    public function getMenuStatistics(int $branchId): array
    {
        $cacheKey = "menu_stats_{$branchId}";
        
        return Cache::remember($cacheKey, 300, function() use ($branchId) { // 5 minutes cache
            $activeMenu = Menu::getActiveMenuForBranch($branchId);
            $nextMenu = Menu::getNextScheduledMenu($branchId);
            
            return [
                'active_menu' => $activeMenu ? [
                    'id' => $activeMenu->id,
                    'name' => $activeMenu->name,
                    'type' => $activeMenu->menu_type,
                    'items_count' => $activeMenu->availableMenuItems()->count(),
                    'available_items_count' => $activeMenu->getAvailableItemsWithStock()->count()
                ] : null,
                'next_menu' => $nextMenu ? [
                    'id' => $nextMenu->id,
                    'name' => $nextMenu->name,
                    'activation_time' => $nextMenu->activation_time?->format('H:i')
                ] : null,
                'total_menus' => Menu::where('branch_id', $branchId)->count(),
                'upcoming_transitions' => count($this->getUpcomingTransitions($branchId, 1))
            ];
        });
    }

    /**
     * Clear menu statistics cache
     */
    public function clearMenuStatsCache(int $branchId): void
    {
        Cache::forget("menu_stats_{$branchId}");
    }
}
