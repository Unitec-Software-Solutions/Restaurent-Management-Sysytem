<?php

namespace App\Services;

use App\Models\ItemMaster;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\Recipe;
use App\Models\Branch;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ProductCatalogService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get unified product catalog for a branch
     */
    public function getBranchCatalog(int $branchId, array $filters = []): array
    {
        $categories = $this->getActiveCategories($branchId, $filters);
        $menuItems = $this->getMenuItemsWithAvailability($branchId, $filters);
        $inventoryItems = $this->getInventoryItems($branchId, $filters);

        return [
            'categories' => $categories,
            'menu_items' => $menuItems,
            'inventory_items' => $inventoryItems,
            'summary' => [
                'total_categories' => count($categories),
                'total_menu_items' => count($menuItems),
                'available_menu_items' => count(array_filter($menuItems, fn($item) => $item['available'])),
                'total_inventory_items' => count($inventoryItems),
                'low_stock_items' => count(array_filter($inventoryItems, fn($item) => $item['is_low_stock'])),
            ]
        ];
    }

    /**
     * Get active menu categories for branch
     */
    private function getActiveCategories(int $branchId, array $filters = []): array
    {
        $query = MenuCategory::active()
            ->currentlyAvailable()
            ->where('branch_id', $branchId)
            ->ordered()
            ->with(['menuItems' => function($q) use ($branchId) {
                $q->active()->available()->withCurrentStock($branchId);
            }]);

        if (isset($filters['category_type'])) {
            $query->where('name', 'like', '%' . $filters['category_type'] . '%');
        }

        return $query->get()->map(function($category) use ($branchId) {
            $availableItems = $category->menuItems->filter(function($item) use ($branchId) {
                return $item->checkAvailability($branchId)['available'];
            });

            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'image_path' => $category->image_path,
                'display_order' => $category->display_order,
                'total_items' => $category->menuItems->count(),
                'available_items' => $availableItems->count(),
                'availability_status' => $category->availability_status,
                'next_available' => $category->getNextAvailableTime(),
            ];
        })->toArray();
    }

    /**
     * Get menu items with real-time availability
     */
    private function getMenuItemsWithAvailability(int $branchId, array $filters = []): array
    {
        $query = MenuItem::with(['category', 'recipes.ingredient'])
            ->where('branch_id', $branchId)
            ->active();

        if (isset($filters['category_id'])) {
            $query->where('menu_category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['availability']) && $filters['availability'] === 'available') {
            $query->inStock($branchId);
        }

        return $query->get()->map(function($item) use ($branchId) {
            $availability = $item->checkAvailability($branchId);
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'image_path' => $item->image_path,
                'category' => $item->category->name ?? 'Uncategorized',
                'category_id' => $item->menu_category_id,
                'preparation_time' => $item->preparation_time,
                'station' => $item->station,
                'is_vegetarian' => $item->is_vegetarian,
                'contains_alcohol' => $item->contains_alcohol,
                'allergens' => $item->allergens,
                'available' => $availability['available'],
                'max_quantity' => $availability['max_quantity'],
                'availability_status' => $item->availability_status,
                'stock_percentage' => $item->stock_percentage,
                'limiting_factor' => $availability['limiting_factor'],
                'missing_ingredients' => $availability['missing_ingredients'],
                'recipes_count' => $item->recipes->count(),
                'estimated_cost' => $item->estimated_cost,
                'profit_margin' => $item->profit_margin,
            ];
        })->toArray();
    }

    /**
     * Get inventory items for direct sales
     */
    private function getInventoryItems(int $branchId, array $filters = []): array
    {
        $query = ItemMaster::with(['category', 'transactions' => function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }])
            ->where('branch_id', $branchId)
            ->where('is_active', true);

        if (isset($filters['category_id'])) {
            $query->where('item_category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['sellable_only']) && $filters['sellable_only']) {
            $query->where('selling_price', '>', 0);
        }

        return $query->get()->map(function($item) use ($branchId) {
            $currentStock = $this->inventoryService->getCurrentStock($item->id, $branchId);
            $isLowStock = $this->inventoryService->isLowStock($item->id, $branchId);
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'item_code' => $item->item_code,
                'category' => $item->category->name ?? 'Uncategorized',
                'unit_of_measurement' => $item->unit_of_measurement,
                'buying_price' => $item->buying_price,
                'selling_price' => $item->selling_price,
                'current_stock' => $currentStock,
                'reorder_level' => $item->reorder_level,
                'is_low_stock' => $isLowStock,
                'is_perishable' => $item->is_perishable,
                'shelf_life_in_days' => $item->shelf_life_in_days,
                'is_menu_item' => $item->is_menu_item,
                'can_sell_directly' => $item->selling_price > 0,
                'stock_status' => $this->getStockStatus($currentStock, $item->reorder_level),
            ];
        })->toArray();
    }

    /**
     * Get stock status indicator
     */
    private function getStockStatus(float $currentStock, float $reorderLevel): string
    {
        if ($currentStock <= 0) return 'out_of_stock';
        if ($currentStock <= $reorderLevel) return 'low_stock';
        if ($currentStock <= $reorderLevel * 2) return 'medium_stock';
        return 'good_stock';
    }

    /**
     * Create a new menu item with recipes
     */
    public function createMenuItem(array $data): array
    {
        DB::beginTransaction();

        try {
            // Create menu item
            $menuItem = MenuItem::create([
                'menu_category_id' => $data['menu_category_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'image_path' => $data['image_path'] ?? null,
                'is_available' => $data['is_available'] ?? true,
                'requires_preparation' => $data['requires_preparation'] ?? true,
                'preparation_time' => $data['preparation_time'] ?? 15,
                'station' => $data['station'] ?? 'kitchen',
                'is_vegetarian' => $data['is_vegetarian'] ?? false,
                'contains_alcohol' => $data['contains_alcohol'] ?? false,
                'allergens' => $data['allergens'] ?? [],
                'branch_id' => $data['branch_id'],
                'organization_id' => $data['organization_id'],
                'estimated_cost' => $data['estimated_cost'] ?? 0,
                'profit_margin' => $data['profit_margin'] ?? 0,
                'max_daily_quantity' => $data['max_daily_quantity'] ?? null,
            ]);

            // Create recipes if provided
            if (isset($data['recipes']) && is_array($data['recipes'])) {
                foreach ($data['recipes'] as $recipeData) {
                    Recipe::create([
                        'menu_item_id' => $menuItem->id,
                        'ingredient_item_id' => $recipeData['ingredient_item_id'],
                        'quantity_needed' => $recipeData['quantity_needed'],
                        'unit' => $recipeData['unit'] ?? 'g',
                        'waste_percentage' => $recipeData['waste_percentage'] ?? 0,
                        'notes' => $recipeData['notes'] ?? null,
                    ]);
                }

                // Calculate estimated cost based on recipes
                $this->updateMenuItemCost($menuItem);
            }

            DB::commit();

            return [
                'success' => true,
                'menu_item' => $menuItem->load(['category', 'recipes.ingredient']),
                'message' => 'Menu item created successfully'
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create menu item', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create menu item: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update menu item cost based on current ingredient prices
     */
    public function updateMenuItemCost(MenuItem $menuItem): void
    {
        $totalCost = 0;

        foreach ($menuItem->recipes as $recipe) {
            $ingredientCost = $recipe->ingredient->buying_price ?? 0;
            $recipeCost = $ingredientCost * $recipe->actual_quantity_needed;
            $totalCost += $recipeCost;
        }

        $menuItem->update([
            'estimated_cost' => round($totalCost, 2),
            'profit_margin' => round((($menuItem->price - $totalCost) / $menuItem->price) * 100, 2)
        ]);
    }

    /**
     * Batch update menu availability based on stock levels
     */
    public function updateMenuAvailability(int $branchId): array
    {
        $menuItems = MenuItem::with('recipes.ingredient')
            ->where('branch_id', $branchId)
            ->active()
            ->get();

        $updated = [];
        $unavailable = [];

        foreach ($menuItems as $item) {
            $availability = $item->checkAvailability($branchId);
            
            $shouldBeAvailable = $availability['available'] && $item->is_active;
            
            if ($item->is_available !== $shouldBeAvailable) {
                $item->update(['is_available' => $shouldBeAvailable]);
                
                if ($shouldBeAvailable) {
                    $updated[] = $item->name;
                } else {
                    $unavailable[] = [
                        'name' => $item->name,
                        'reason' => $availability['limiting_factor'] ?? 'Stock unavailable'
                    ];
                }
            }
        }

        return [
            'updated_count' => count($updated) + count($unavailable),
            'made_available' => $updated,
            'made_unavailable' => $unavailable,
        ];
    }

    /**
     * Get product suggestions for cross-selling
     */
    public function getProductSuggestions(array $cartItems, int $branchId): array
    {
        $suggestions = [];
        $cartCategoryIds = [];

        // Analyze current cart
        foreach ($cartItems as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            if ($menuItem) {
                $cartCategoryIds[] = $menuItem->menu_category_id;
            }
        }

        $cartCategoryIds = array_unique($cartCategoryIds);

        // Suggest complementary items from different categories
        $complementaryCategories = $this->getComplementaryCategories($cartCategoryIds);
        
        foreach ($complementaryCategories as $categoryId) {
            $items = MenuItem::where('menu_category_id', $categoryId)
                ->where('branch_id', $branchId)
                ->active()
                ->available()
                ->inStock($branchId)
                ->limit(3)
                ->get();

            foreach ($items as $item) {
                $availability = $item->checkAvailability($branchId);
                if ($availability['available']) {
                    $suggestions[] = [
                        'menu_item_id' => $item->id,
                        'name' => $item->name,
                        'price' => $item->price,
                        'category' => $item->category->name,
                        'reason' => 'Frequently bought together',
                    ];
                }
            }
        }

        return array_slice($suggestions, 0, 5); // Limit to 5 suggestions
    }

    /**
     * Get complementary categories based on current selection
     */
    private function getComplementaryCategories(array $categoryIds): array
    {
        // Simple complementary logic - can be enhanced with ML/analytics
        $complementaryMap = [
            1 => [2, 3], // Main courses → Beverages, Desserts
            2 => [1, 4], // Beverages → Main courses, Appetizers
            3 => [2],    // Desserts → Beverages
            4 => [1, 2], // Appetizers → Main courses, Beverages
        ];

        $suggestions = [];
        foreach ($categoryIds as $categoryId) {
            if (isset($complementaryMap[$categoryId])) {
                $suggestions = array_merge($suggestions, $complementaryMap[$categoryId]);
            }
        }

        return array_unique(array_diff($suggestions, $categoryIds));
    }

    /**
     * Get product analytics for dashboard
     */
    public function getProductAnalytics(int $branchId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'top_selling_items' => $this->getTopSellingItems($branchId, $startDate),
            'low_stock_alerts' => $this->inventoryService->getLowStockItems($branchId),
            'profit_analysis' => $this->getProfitAnalysis($branchId, $startDate),
            'availability_stats' => $this->getAvailabilityStats($branchId),
        ];
    }

    /**
     * Get top selling menu items
     */
    private function getTopSellingItems(int $branchId, $startDate): array
    {
        // This would require order data analysis
        // Placeholder implementation
        return MenuItem::where('branch_id', $branchId)
            ->active()
            ->withCount(['orderItems' => function($q) use ($startDate) {
                $q->whereHas('order', function($order) use ($startDate) {
                    $order->where('created_at', '>=', $startDate);
                });
            }])
            ->orderBy('order_items_count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get profit analysis
     */
    private function getProfitAnalysis(int $branchId, $startDate): array
    {
        $menuItems = MenuItem::where('branch_id', $branchId)->active()->get();
        
        $totalEstimatedCost = $menuItems->sum('estimated_cost');
        $totalPrice = $menuItems->sum('price');
        $averageMargin = $menuItems->avg('profit_margin');

        return [
            'total_estimated_cost' => $totalEstimatedCost,
            'total_selling_price' => $totalPrice,
            'average_profit_margin' => round($averageMargin, 2),
            'potential_profit' => $totalPrice - $totalEstimatedCost,
        ];
    }

    /**
     * Get availability statistics
     */
    private function getAvailabilityStats(int $branchId): array
    {
        $menuItems = MenuItem::where('branch_id', $branchId)->active()->get();
        
        $available = 0;
        $lowStock = 0;
        $outOfStock = 0;

        foreach ($menuItems as $item) {
            $availability = $item->checkAvailability($branchId);
            if ($availability['available']) {
                if ($availability['max_quantity'] <= 5) {
                    $lowStock++;
                } else {
                    $available++;
                }
            } else {
                $outOfStock++;
            }
        }

        return [
            'total_items' => $menuItems->count(),
            'available' => $available,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'availability_percentage' => round(($available / max(1, $menuItems->count())) * 100, 1),
        ];
    }
}
