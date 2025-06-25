<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Branch;
use App\Models\MenuItem;
use App\Events\MenuActivated;
use App\Events\MenuDeactivated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Phase 2: Enhanced Menu Scheduling Service
 * Handles daily menu determination, special overrides, and time-based availability
 */
class EnhancedMenuSchedulingService
{
    /**
     * Get the menu that should be active for a specific date and time
     */
    public function getActiveMenuForDateTime(int $branchId, Carbon $dateTime): ?Menu
    {
        $cacheKey = "active_menu_{$branchId}_{$dateTime->format('Y-m-d_H-i')}";
        
        return Cache::remember($cacheKey, 300, function() use ($branchId, $dateTime) {
            // 1. Check for special menu overrides first (highest priority)
            $specialMenu = $this->getSpecialMenuOverride($branchId, $dateTime);
            if ($specialMenu) {
                return $specialMenu;
            }

            // 2. Check for regular scheduled menus
            $scheduledMenu = $this->getScheduledMenu($branchId, $dateTime);
            if ($scheduledMenu) {
                return $scheduledMenu;
            }

            // 3. Fall back to default menu for the branch
            return $this->getDefaultMenu($branchId, $dateTime);
        });
    }

    /**
     * Check for special menu overrides (holidays, events, promotions)
     */
    public function getSpecialMenuOverride(int $branchId, Carbon $dateTime): ?Menu
    {
        return Menu::where('branch_id', $branchId)
            ->where('menu_type', 'special')
            ->where('is_active', true)
            ->where('valid_from', '<=', $dateTime->format('Y-m-d'))
            ->where('valid_until', '>=', $dateTime->format('Y-m-d'))
            ->where(function($query) use ($dateTime) {
                $query->whereNull('start_time')
                    ->orWhere('start_time', '<=', $dateTime->format('H:i'));
            })
            ->where(function($query) use ($dateTime) {
                $query->whereNull('end_time')
                    ->orWhere('end_time', '>=', $dateTime->format('H:i'));
            })
            ->whereJsonContains('available_days', strtolower($dateTime->format('l')))
            ->orderBy('priority', 'desc')
            ->first();
    }

    /**
     * Get regularly scheduled menu
     */
    public function getScheduledMenu(int $branchId, Carbon $dateTime): ?Menu
    {
        return Menu::where('branch_id', $branchId)
            ->whereIn('menu_type', ['breakfast', 'lunch', 'dinner', 'all_day'])
            ->where('is_active', true)
            ->where('valid_from', '<=', $dateTime->format('Y-m-d'))
            ->where('valid_until', '>=', $dateTime->format('Y-m-d'))
            ->where(function($query) use ($dateTime) {
                $query->whereNull('start_time')
                    ->orWhere('start_time', '<=', $dateTime->format('H:i'));
            })
            ->where(function($query) use ($dateTime) {
                $query->whereNull('end_time')
                    ->orWhere('end_time', '>=', $dateTime->format('H:i'));
            })
            ->whereJsonContains('available_days', strtolower($dateTime->format('l')))
            ->orderBy('priority', 'desc')
            ->orderBy('start_time')
            ->first();
    }

    /**
     * Get default fallback menu
     */
    public function getDefaultMenu(int $branchId, Carbon $dateTime): ?Menu
    {
        return Menu::where('branch_id', $branchId)
            ->where('menu_type', 'default')
            ->where('is_active', true)
            ->whereJsonContains('available_days', strtolower($dateTime->format('l')))
            ->first();
    }

    /**
     * Create special menu override
     */
    public function createSpecialMenuOverride(array $menuData): Menu
    {
        DB::beginTransaction();
        try {
            $menu = Menu::create(array_merge($menuData, [
                'menu_type' => 'special',
                'priority' => $menuData['priority'] ?? 100,
                'is_active' => true,
                'created_by' => auth('admin')->id()
            ]));

            // Attach menu items if provided
            if (isset($menuData['menu_items'])) {
                $menu->menuItems()->attach($menuData['menu_items']);
            }

            // Log the override creation
            Log::info('Special menu override created', [
                'menu_id' => $menu->id,
                'branch_id' => $menu->branch_id,
                'valid_from' => $menu->valid_from,
                'valid_until' => $menu->valid_until,
                'priority' => $menu->priority
            ]);

            DB::commit();
            return $menu;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create special menu override', [
                'error' => $e->getMessage(),
                'data' => $menuData
            ]);
            throw $e;
        }
    }

    /**
     * Get menu validity periods with conflicts detection
     */
    public function getMenuValidityPeriods(int $branchId, Carbon $startDate, Carbon $endDate): array
    {
        $periods = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayPeriods = $this->getDayMenuPeriods($branchId, $currentDate);
            $periods[$currentDate->format('Y-m-d')] = $dayPeriods;
            $currentDate->addDay();
        }

        return $periods;
    }

    /**
     * Get menu periods for a specific day
     */
    public function getDayMenuPeriods(int $branchId, Carbon $date): array
    {
        $periods = [];
        $menus = Menu::where('branch_id', $branchId)
            ->where('valid_from', '<=', $date->format('Y-m-d'))
            ->where('valid_until', '>=', $date->format('Y-m-d'))
            ->whereJsonContains('available_days', strtolower($date->format('l')))
            ->orderBy('priority', 'desc')
            ->orderBy('start_time')
            ->get();

        foreach ($menus as $menu) {
            $periods[] = [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'menu_type' => $menu->menu_type,
                'start_time' => $menu->start_time,
                'end_time' => $menu->end_time,
                'priority' => $menu->priority,
                'is_active' => $menu->is_active
            ];
        }

        return $periods;
    }

    /**
     * Activate menu for current time
     */
    public function activateCurrentMenu(int $branchId): array
    {
        $now = now();
        $currentMenu = Menu::getActiveMenuForBranch($branchId);
        $shouldBeActiveMenu = $this->getActiveMenuForDateTime($branchId, $now);

        $result = [
            'branch_id' => $branchId,
            'previous_menu' => $currentMenu?->name,
            'new_menu' => $shouldBeActiveMenu?->name,
            'action' => 'none',
            'success' => true,
            'message' => 'No changes needed'
        ];

        try {
            // If different menu should be active
            if (!$currentMenu || !$shouldBeActiveMenu || $currentMenu->id !== $shouldBeActiveMenu->id) {
                
                // Deactivate current menu
                if ($currentMenu) {
                    $this->deactivateMenu($currentMenu);
                    $result['action'] = 'deactivated';
                    $result['message'] = "Deactivated menu: {$currentMenu->name}";
                }

                // Activate new menu
                if ($shouldBeActiveMenu) {
                    $this->activateMenu($shouldBeActiveMenu);
                    $result['action'] = $result['action'] === 'deactivated' ? 'switched' : 'activated';
                    $result['message'] = "Activated menu: {$shouldBeActiveMenu->name}";
                }
            }

        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = "Error: " . $e->getMessage();
            
            Log::error("Menu activation failed", [
                'branch_id' => $branchId,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Activate a specific menu
     */
    public function activateMenu(Menu $menu): void
    {
        DB::transaction(function() use ($menu) {
            // Deactivate other menus in the same branch
            Menu::where('branch_id', $menu->branch_id)
                ->where('id', '!=', $menu->id)
                ->update(['is_active' => false, 'deactivated_at' => now()]);

            // Activate the menu
            $menu->update([
                'is_active' => true,
                'activated_at' => now()
            ]);

            // Clear cache
            Cache::forget("active_menu_branch_{$menu->branch_id}");

            // Fire event
            event(new MenuActivated($menu));

            Log::info("Menu activated", [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'branch_id' => $menu->branch_id
            ]);
        });
    }

    /**
     * Deactivate a specific menu
     */
    public function deactivateMenu(Menu $menu): void
    {
        $menu->update([
            'is_active' => false,
            'deactivated_at' => now()
        ]);

        // Clear cache
        Cache::forget("active_menu_branch_{$menu->branch_id}");

        // Fire event
        event(new MenuDeactivated($menu));

        Log::info("Menu deactivated", [
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'branch_id' => $menu->branch_id
        ]);
    }

    /**
     * Check menu availability for items
     */
    public function checkMenuItemAvailability(Menu $menu): array
    {
        $unavailableItems = [];
        $lowStockItems = [];

        foreach ($menu->menuItems as $item) {
            // Check if item is marked as available
            if (!$item->is_available) {
                $unavailableItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'reason' => 'Item marked as unavailable'
                ];
                continue;
            }

            // Check stock levels
            if ($item->track_inventory && $item->current_stock !== null) {
                if ($item->current_stock <= 0) {
                    $unavailableItems[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'reason' => 'Out of stock'
                    ];
                } elseif ($item->current_stock <= $item->low_stock_threshold) {
                    $lowStockItems[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'current_stock' => $item->current_stock,
                        'threshold' => $item->low_stock_threshold
                    ];
                }
            }
        }

        return [
            'total_items' => $menu->menuItems->count(),
            'available_items' => $menu->menuItems->count() - count($unavailableItems),
            'unavailable_items' => $unavailableItems,
            'low_stock_items' => $lowStockItems
        ];
    }

    /**
     * Get upcoming menu transitions for next 24 hours
     */
    public function getUpcomingTransitions(int $branchId, int $hours = 24): array
    {
        $transitions = [];
        $endTime = now()->addHours($hours);
        $currentTime = now();

        while ($currentTime->lt($endTime)) {
            $menu = $this->getActiveMenuForDateTime($branchId, $currentTime);
            
            if ($menu) {
                // Check for transition at start time
                if ($menu->start_time) {
                    $transitionTime = $currentTime->copy()->setTimeFromTimeString($menu->start_time);
                    if ($transitionTime->gte(now()) && $transitionTime->lte($endTime)) {
                        $transitions[] = [
                            'time' => $transitionTime,
                            'action' => 'activate',
                            'menu' => $menu->name,
                            'menu_id' => $menu->id
                        ];
                    }
                }

                // Check for transition at end time
                if ($menu->end_time) {
                    $transitionTime = $currentTime->copy()->setTimeFromTimeString($menu->end_time);
                    if ($transitionTime->gte(now()) && $transitionTime->lte($endTime)) {
                        $transitions[] = [
                            'time' => $transitionTime,
                            'action' => 'deactivate',
                            'menu' => $menu->name,
                            'menu_id' => $menu->id
                        ];
                    }
                }
            }

            $currentTime->addHour();
        }

        // Sort by time
        usort($transitions, function($a, $b) {
            return $a['time']->timestamp - $b['time']->timestamp;
        });

        return $transitions;
    }

    /**
     * Create default menus for a new branch
     */
    public function createDefaultMenusForBranch(int $branchId, int $organizationId): array
    {
        $defaultMenus = [
            [
                'name' => 'Breakfast Menu',
                'menu_type' => 'breakfast',
                'start_time' => '06:00',
                'end_time' => '11:00',
                'priority' => 1
            ],
            [
                'name' => 'Lunch Menu',
                'menu_type' => 'lunch',
                'start_time' => '11:00',
                'end_time' => '16:00',
                'priority' => 1
            ],
            [
                'name' => 'Dinner Menu',
                'menu_type' => 'dinner',
                'start_time' => '17:00',
                'end_time' => '23:00',
                'priority' => 1
            ],
            [
                'name' => 'Default Menu',
                'menu_type' => 'default',
                'start_time' => null,
                'end_time' => null,
                'priority' => 0
            ]
        ];

        $created = [];
        
        foreach ($defaultMenus as $menuData) {
            $menu = Menu::create(array_merge($menuData, [
                'branch_id' => $branchId,
                'organization_id' => $organizationId,
                'valid_from' => now()->format('Y-m-d'),
                'valid_until' => now()->addYear()->format('Y-m-d'),
                'available_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                'is_active' => $menuData['menu_type'] === 'default',
                'created_by' => auth('admin')->id()
            ]));

            $created[] = $menu;
        }

        return $created;
    }
}
