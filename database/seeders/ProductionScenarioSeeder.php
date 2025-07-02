<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionOrderIngredient;
use App\Models\ItemMaster;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;

class ProductionScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates comprehensive production workflow scenarios:
     * - Regular production orders
     * - Rush/urgent production
     * - Batch production for events
     * - Production with ingredient shortages
     * - Quality control issues
     * - Seasonal production cycles
     */
    public function run(): void
    {
        $this->command->info('🏭 Seeding production workflow scenarios...');

        // Get required data
        $organizations = Organization::with('branches')->get();
        $menuItems = ItemMaster::where('is_menu_item', true)->get();
        $ingredients = ItemMaster::where('is_menu_item', false)
                                ->where('is_active', true)
                                ->get();

        if ($organizations->isEmpty() || $menuItems->isEmpty() || $ingredients->isEmpty()) {
            $this->command->warn('Organizations, menu items, and ingredients are required.');
            return;
        }

        foreach ($organizations as $organization) {
            foreach ($organization->branches as $branch) {
                $this->createProductionScenarios($organization, $branch, $menuItems, $ingredients);
            }
        }

        $this->command->info('✅ Production scenario seeding completed!');
    }

    private function createProductionScenarios($organization, $branch, $menuItems, $ingredients)
    {
        $this->command->info("Creating production scenarios for: {$branch->name}");

        // Scenario 1: Regular daily production
        $this->createRegularProduction($organization, $branch, $menuItems, $ingredients);

        // Scenario 2: Rush orders for events
        $this->createRushProduction($organization, $branch, $menuItems, $ingredients);

        // Scenario 3: Batch production for peak hours
        $this->createBatchProduction($organization, $branch, $menuItems, $ingredients);

        // Scenario 4: Production with ingredient shortages
        $this->createShortageProduction($organization, $branch, $menuItems, $ingredients);

        // Scenario 5: Quality control scenarios
        $this->createQualityControlProduction($organization, $branch, $menuItems, $ingredients);

        // Scenario 6: Seasonal production
        $this->createSeasonalProduction($organization, $branch, $menuItems, $ingredients);
    }

    private function createRegularProduction($organization, $branch, $menuItems, $ingredients)
    {
        for ($i = 0; $i < 5; $i++) {
            $productionOrder = ProductionOrder::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'production_order_number' => $this->generateProductionNumber(),
                'production_date' => Carbon::now()->addDays(rand(0, 7)),
                'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
                'priority' => 'normal',
                'notes' => 'Regular daily production for menu items',
                'requested_by' => $this->getRandomUser(),
                'approved_by' => rand(0, 1) ? $this->getRandomUser() : null,
                'started_at' => rand(0, 1) ? Carbon::now()->subHours(rand(1, 8)) : null,
                'completed_at' => rand(0, 1) ? Carbon::now()->subHours(rand(0, 4)) : null,
            ]);

            // Add 2-4 items to produce
            $itemsToProduce = $menuItems->random(rand(2, 4));
            foreach ($itemsToProduce as $item) {
                $quantityToProduce = rand(10, 50);
                
                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'item_id' => $item->id,
                    'quantity_to_produce' => $quantityToProduce,
                    'quantity_produced' => $productionOrder->status === 'completed' ? $quantityToProduce : rand(0, $quantityToProduce),
                    'quantity_wasted' => rand(0, 2),
                    'notes' => 'Standard production quantity',
                ]);

                // Add ingredients for this item
                $this->addIngredientsForItem($productionOrder, $item, $ingredients, $quantityToProduce);
            }
        }
    }

    private function createRushProduction($organization, $branch, $menuItems, $ingredients)
    {
        for ($i = 0; $i < 3; $i++) {
            $productionOrder = ProductionOrder::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'production_order_number' => $this->generateProductionNumber(),
                'production_date' => Carbon::now()->addHours(rand(2, 12)),
                'status' => fake()->randomElement(['pending', 'in_progress']),
                'priority' => 'urgent',
                'notes' => 'Rush order for special event - expedite production',
                'requested_by' => $this->getRandomUser(),
                'approved_by' => $this->getRandomUser(),
                'deadline' => Carbon::now()->addHours(rand(4, 24)),
            ]);

            // Rush orders typically have fewer items but higher quantities
            $itemsToProduce = $menuItems->random(rand(1, 3));
            foreach ($itemsToProduce as $item) {
                $quantityToProduce = rand(50, 150);
                
                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'item_id' => $item->id,
                    'quantity_to_produce' => $quantityToProduce,
                    'quantity_produced' => $productionOrder->status === 'in_progress' ? rand(0, $quantityToProduce * 0.6) : 0,
                    'quantity_wasted' => 0, // Rush orders typically have minimal waste
                    'notes' => 'RUSH ORDER - Priority production required',
                ]);

                $this->addIngredientsForItem($productionOrder, $item, $ingredients, $quantityToProduce);
            }
        }
    }

    private function createBatchProduction($organization, $branch, $menuItems, $ingredients)
    {
        for ($i = 0; $i < 2; $i++) {
            $productionOrder = ProductionOrder::create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'production_order_number' => $this->generateProductionNumber(),
                'production_date' => Carbon::now()->addDays(rand(1, 3)),
                'status' => fake()->randomElement(['pending', 'approved']),
                'priority' => 'high',
                'notes' => 'Batch production for weekend peak hours',
                'requested_by' => $this->getRandomUser(),
                'approved_by' => $this->getRandomUser(),
                'production_type' => 'batch',
            ]);

            // Batch production - high quantities of popular items
            $popularItems = $menuItems->where('is_popular', true)->take(3);
            if ($popularItems->isEmpty()) {
                $popularItems = $menuItems->random(3);
            }

            foreach ($popularItems as $item) {
                $quantityToProduce = rand(100, 300);
                
                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'item_id' => $item->id,
                    'quantity_to_produce' => $quantityToProduce,
                    'quantity_produced' => 0,
                    'quantity_wasted' => 0,
                    'notes' => 'Batch production for peak demand period',
                ]);

                $this->addIngredientsForItem($productionOrder, $item, $ingredients, $quantityToProduce);
            }
        }
    }

    private function createShortageProduction($organization, $branch, $menuItems, $ingredients)
    {
        $productionOrder = ProductionOrder::create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'production_order_number' => $this->generateProductionNumber(),
            'production_date' => Carbon::now()->addDays(1),
            'status' => 'on_hold',
            'priority' => 'normal',
            'notes' => 'Production on hold due to ingredient shortages',
            'requested_by' => $this->getRandomUser(),
            'hold_reason' => 'Waiting for ingredient delivery',
        ]);

        $itemsToProduce = $menuItems->random(2);
        foreach ($itemsToProduce as $item) {
            $quantityToProduce = rand(20, 60);
            
            ProductionOrderItem::create([
                'production_order_id' => $productionOrder->id,
                'item_id' => $item->id,
                'quantity_to_produce' => $quantityToProduce,
                'quantity_produced' => 0,
                'quantity_wasted' => 0,
                'notes' => 'Cannot proceed - awaiting ingredient delivery',
            ]);

            // Add ingredients with shortage flags
            $requiredIngredients = $ingredients->random(rand(3, 6));
            foreach ($requiredIngredients as $ingredient) {
                ProductionOrderIngredient::create([
                    'production_order_id' => $productionOrder->id,
                    'ingredient_item_id' => $ingredient->id,
                    'planned_quantity' => fake()->randomFloat(2, 1, 10),
                    'issued_quantity' => 0,
                    'consumed_quantity' => 0,
                    'returned_quantity' => 0,
                    'unit_of_measurement' => $ingredient->unit_of_measurement,
                    'notes' => 'SHORTAGE: Insufficient stock available',
                    'is_manually_added' => false,
                ]);
            }
        }
    }

    private function createQualityControlProduction($organization, $branch, $menuItems, $ingredients)
    {
        $productionOrder = ProductionOrder::create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'production_order_number' => $this->generateProductionNumber(),
            'production_date' => Carbon::now()->subDays(rand(1, 5)),
            'status' => 'quality_check',
            'priority' => 'normal',
            'notes' => 'Production completed - undergoing quality control inspection',
            'requested_by' => $this->getRandomUser(),
            'approved_by' => $this->getRandomUser(),
            'started_at' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()->subDays(1),
            'quality_check_required' => true,
        ]);

        $itemsToProduce = $menuItems->random(2);
        foreach ($itemsToProduce as $item) {
            $quantityToProduce = rand(30, 80);
            $quantityProduced = $quantityToProduce - rand(2, 8); // Some waste in QC
            
            ProductionOrderItem::create([
                'production_order_id' => $productionOrder->id,
                'item_id' => $item->id,
                'quantity_to_produce' => $quantityToProduce,
                'quantity_produced' => $quantityProduced,
                'quantity_wasted' => $quantityToProduce - $quantityProduced,
                'notes' => 'Quality control rejected ' . ($quantityToProduce - $quantityProduced) . ' units',
            ]);

            $this->addIngredientsForItem($productionOrder, $item, $ingredients, $quantityToProduce, true);
        }
    }

    private function createSeasonalProduction($organization, $branch, $menuItems, $ingredients)
    {
        $productionOrder = ProductionOrder::create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'production_order_number' => $this->generateProductionNumber(),
            'production_date' => Carbon::now()->addWeeks(2),
            'status' => 'planned',
            'priority' => 'low',
            'notes' => 'Seasonal menu items for upcoming holiday period',
            'requested_by' => $this->getRandomUser(),
            'production_type' => 'seasonal',
            'season' => fake()->randomElement(['spring', 'summer', 'autumn', 'winter']),
        ]);

        // Seasonal items (assume some menu items are marked as seasonal)
        $seasonalItems = $menuItems->random(rand(2, 4));
        foreach ($seasonalItems as $item) {
            $quantityToProduce = rand(50, 200);
            
            ProductionOrderItem::create([
                'production_order_id' => $productionOrder->id,
                'item_id' => $item->id,
                'quantity_to_produce' => $quantityToProduce,
                'quantity_produced' => 0,
                'quantity_wasted' => 0,
                'notes' => 'Seasonal production for holiday menu',
            ]);

            $this->addIngredientsForItem($productionOrder, $item, $ingredients, $quantityToProduce);
        }
    }

    private function addIngredientsForItem($productionOrder, $item, $ingredients, $quantityToProduce, $completed = false)
    {
        // Randomly select 3-6 ingredients for this item
        $requiredIngredients = $ingredients->random(rand(3, 6));
        
        foreach ($requiredIngredients as $ingredient) {
            $plannedQty = fake()->randomFloat(2, 0.5, 5.0) * $quantityToProduce / 10; // Scale with production quantity
            $issuedQty = $completed ? $plannedQty : ($productionOrder->status === 'in_progress' ? $plannedQty * 0.8 : 0);
            $consumedQty = $completed ? $issuedQty * 0.95 : ($issuedQty * 0.6); // 95% efficiency when completed
            $returnedQty = $issuedQty - $consumedQty;

            ProductionOrderIngredient::create([
                'production_order_id' => $productionOrder->id,
                'ingredient_item_id' => $ingredient->id,
                'planned_quantity' => $plannedQty,
                'issued_quantity' => $issuedQty,
                'consumed_quantity' => $consumedQty,
                'returned_quantity' => $returnedQty,
                'unit_of_measurement' => $ingredient->unit_of_measurement,
                'notes' => $completed ? 'Ingredient consumption recorded' : 'Ingredient requirement calculated',
                'is_manually_added' => rand(0, 1) == 1,
            ]);
        }
    }

    private function generateProductionNumber()
    {
        return 'PRO-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function getRandomUser()
    {
        // Try to get a random user, fallback to 1 if no users exist
        $userCount = User::count();
        return $userCount > 0 ? rand(1, $userCount) : 1;
    }
}
