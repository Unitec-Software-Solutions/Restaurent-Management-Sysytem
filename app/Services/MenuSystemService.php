<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class MenuSystemService
{
    public function installSchedulingSystem()
    {
        $this->createMenuScheduleTables();
        $this->setupDailyMenuLogic();
        $this->implementSpecialMenus();
        $this->addTimeBasedAvailability();
    }

    private function createMenuScheduleTables()
    {
        // Add scheduling columns to menu_items table
        \Illuminate\Support\Facades\Schema::table('menu_items', function ($table) {
            $table->json('availability_schedule')->nullable();
            $table->time('available_from')->nullable();
            $table->time('available_until')->nullable();
            $table->json('day_availability')->nullable(); // Mon, Tue, etc.
            $table->date('special_date')->nullable();
            $table->boolean('is_special_menu')->default(false);
            $table->integer('display_order')->default(0);
        });
    }

    private function setupDailyMenuLogic()
    {
        // Implementation for daily menu determination
    }

    public function getDailyMenu(Branch $branch, ?Carbon $date = null): array
    {
        $date = $date ?? Carbon::now();
        $cacheKey = "daily_menu_{$branch->id}_{$date->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 3600, function () use ($branch, $date) {
            return $this->buildDailyMenu($branch, $date);
        });
    }

    private function buildDailyMenu(Branch $branch, Carbon $date): array
    {
        $currentTime = $date->format('H:i:s');
        $dayOfWeek = strtolower($date->format('l'));

        // 1. Get special menus for this date
        $specialMenus = MenuItem::where('branch_id', $branch->id)
            ->where('is_special_menu', true)
            ->where('special_date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->get();

        if ($specialMenus->isNotEmpty()) {
            return $this->formatMenuResponse($specialMenus, 'special');
        }

        // 2. Get regular menu items available today
        $regularMenus = MenuItem::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->where('is_special_menu', false)
            ->where(function ($query) use ($currentTime) {
                $query->whereNull('available_from')
                    ->orWhere('available_from', '<=', $currentTime);
            })
            ->where(function ($query) use ($currentTime) {
                $query->whereNull('available_until')
                    ->orWhere('available_until', '>=', $currentTime);
            })
            ->where(function ($query) use ($dayOfWeek) {
                $query->whereNull('day_availability')
                    ->orWhereJsonContains('day_availability', $dayOfWeek);
            })
            ->orderBy('display_order')
            ->get();

        return $this->formatMenuResponse($regularMenus, 'regular');
    }

    private function formatMenuResponse($menuItems, $type): array
    {
        $categorizedMenu = [];
        
        foreach ($menuItems as $item) {
            $category = $item->category->name ?? 'Uncategorized';
            
            if (!isset($categorizedMenu[$category])) {
                $categorizedMenu[$category] = [
                    'category_name' => $category,
                    'items' => []
                ];
            }
            
            $categorizedMenu[$category]['items'][] = [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'image' => $item->image,
                'is_available' => $this->checkItemAvailability($item),
                'availability_note' => $this->getAvailabilityNote($item)
            ];
        }

        return [
            'type' => $type,
            'date' => now()->format('Y-m-d'),
            'categories' => array_values($categorizedMenu)
        ];
    }

    public function checkItemAvailability(MenuItem $item): bool
    {
        // Check inventory levels
        if ($item->requires_inventory_check) {
            return $this->checkInventoryAvailability($item);
        }
        
        return true;
    }

    private function checkInventoryAvailability(MenuItem $item): bool
    {
        // Check if required ingredients are in stock
        foreach ($item->ingredients ?? [] as $ingredient) {
            $inventoryItem = \App\Models\InventoryItem::where('branch_id', $item->branch_id)
                ->where('item_masters_id', $ingredient['item_masters_id'])
                ->first();
                
            if (!$inventoryItem || $inventoryItem->current_stock < $ingredient['required_quantity']) {
                return false;
            }
        }
        
        return true;
    }

    private function getAvailabilityNote(MenuItem $item): ?string
    {
        if (!$this->checkItemAvailability($item)) {
            return 'Currently unavailable';
        }
        
        if ($item->available_until) {
            $until = Carbon::parse($item->available_until);
            if ($until->diffInHours(now()) <= 2) {
                return "Available until {$until->format('H:i')}";
            }
        }
        
        return null;
    }

    private function implementSpecialMenus()
    {
        // Special menu implementation
    }

    private function addTimeBasedAvailability()
    {
        // Time-based availability implementation
    }

    public function createSpecialMenu(Branch $branch, Carbon $date, array $menuItems): void
    {
        foreach ($menuItems as $itemData) {
            MenuItem::create([
                'branch_id' => $branch->id,
                'name' => $itemData['name'],
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                'is_special_menu' => true,
                'special_date' => $date->format('Y-m-d'),
                'is_active' => true,
                'display_order' => $itemData['display_order'] ?? 0
            ]);
        }
        
        // Clear cache
        Cache::forget("daily_menu_{$branch->id}_{$date->format('Y-m-d')}");
    }

    public function scheduleMenuItem(MenuItem $item, array $schedule): void
    {
        $item->update([
            'availability_schedule' => $schedule,
            'available_from' => $schedule['time_from'] ?? null,
            'available_until' => $schedule['time_until'] ?? null,
            'day_availability' => $schedule['days'] ?? null
        ]);
        
        // Clear related caches
        $this->clearMenuCache($item->branch_id);
    }

    private function clearMenuCache(int $branchId): void
    {
        $dates = collect(range(0, 7))->map(function ($days) {
            return Carbon::now()->addDays($days)->format('Y-m-d');
        });
        
        foreach ($dates as $date) {
            Cache::forget("daily_menu_{$branchId}_{$date}");
        }
    }
}