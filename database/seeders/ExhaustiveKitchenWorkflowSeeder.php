<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\KitchenStation;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\ItemMaster;
use App\Models\Employee;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExhaustiveKitchenWorkflowSeeder extends Seeder
{
    use WithoutModelEvents;

    private $branches;
    private $kitchenStations;
    private $kitchenStaff;
    private $menuItems;
    private $workflowData = [];

    /**
     * Seed exhaustive kitchen workflow scenarios covering all operational complexities
     */
    public function run(): void
    {
        $this->command->info('👨‍🍳 Seeding Exhaustive Kitchen Workflow Scenarios...');
        $this->command->info('══════════════════════════════════════════════════════════');
        
        // Initialize test data
        $this->initializeTestData();
        
        try {
            // Phase 1: Kitchen Station Setup
            $this->command->info('🏪 Phase 1: Kitchen Station Configuration');
            $this->seedKitchenStations();
            
            // Phase 2: Basic Workflow Patterns
            $this->command->info('⚙️ Phase 2: Basic Kitchen Workflow Patterns');
            $this->seedBasicWorkflows();
            
            // Phase 3: Order Processing Scenarios
            $this->command->info('📋 Phase 3: Order Processing & Prioritization');
            $this->seedOrderProcessingScenarios();
            
            // Phase 4: Station Coordination
            $this->command->info('🤝 Phase 4: Multi-Station Coordination');
            $this->seedStationCoordination();
            
            // Phase 5: Peak Time Management
            $this->command->info('🔥 Phase 5: Peak Time & Rush Management');
            $this->seedPeakTimeScenarios();
            
            // Phase 6: Special Menu Items
            $this->command->info('✨ Phase 6: Special Menu Item Workflows');
            $this->seedSpecialItemWorkflows();
            
            // Phase 7: Kitchen Emergency Scenarios
            $this->command->info('🚨 Phase 7: Kitchen Emergency & Recovery');
            $this->seedEmergencyScenarios();
            
            // Phase 8: Quality Control Workflows
            $this->command->info('✅ Phase 8: Quality Control & Standards');
            $this->seedQualityControlWorkflows();
            
            // Phase 9: Cross-Training Scenarios
            $this->command->info('🎓 Phase 9: Cross-Training & Flexibility');
            $this->seedCrossTrainingScenarios();
            
            // Phase 10: Performance Optimization
            $this->command->info('📈 Phase 10: Performance & Efficiency Optimization');
            $this->seedPerformanceScenarios();
            
            $this->displayKitchenSummary();
            
        } catch (\Exception $e) {
            $this->command->error('❌ Kitchen workflow seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function initializeTestData(): void
    {
        $this->branches = Branch::with(['organization'])->get();
        $this->kitchenStations = KitchenStation::all();
        $this->kitchenStaff = Employee::whereHas('employeeRole', function($query) {
            $query->whereIn('name', ['chef', 'cook', 'kitchen_assistant', 'prep_cook']);
        })->get();
        $this->menuItems = ItemMaster::where('is_menu_item', true)->get();
        
        // If no kitchen staff exists, create some
        if ($this->kitchenStaff->isEmpty()) {
            $this->createKitchenStaff();
        }
    }

    private function createKitchenStaff(): void
    {
        $positions = [
            'head_chef' => 'Head Chef',
            'sous_chef' => 'Sous Chef', 
            'line_cook' => 'Line Cook',
            'prep_cook' => 'Prep Cook',
            'kitchen_assistant' => 'Kitchen Assistant',
            'pastry_chef' => 'Pastry Chef',
            'grill_cook' => 'Grill Cook',
            'salad_cook' => 'Salad Cook'
        ];

        foreach ($this->branches->take(3) as $branch) {
            foreach ($positions as $role => $title) {
                for ($i = 1; $i <= 2; $i++) {
                    Employee::create([
                        'name' => "{$title} {$i}",
                        'email' => "{$role}.{$i}.{$branch->id}@restaurant.com",
                        'phone' => '+94' . str_pad(rand(700000000, 799999999), 9, '0', STR_PAD_LEFT),
                        'position' => $title,
                        'role' => $role,
                        'branch_id' => $branch->id,
                        'organization_id' => $branch->organization_id,
                        'salary' => rand(50000, 150000),
                        'hire_date' => Carbon::now()->subDays(rand(30, 365)),
                        'is_active' => true,
                    ]);
                }
            }
        }
        
        $this->kitchenStaff = Employee::whereHas('employeeRole', function($query) {
            $query->whereIn('name', ['chef', 'cook', 'kitchen_assistant', 'prep_cook']);
        })->get();
    }

    private function seedKitchenStations(): void
    {
        $stationTypes = [
            'hot_kitchen' => [
                'name' => 'Hot Kitchen',
                'description' => 'Main cooking station for hot dishes',
                'equipment' => 'Stoves, grills, fryers, ovens',
                'capacity' => 8,
                'specialties' => ['main_course', 'hot_appetizers', 'soups']
            ],
            'cold_kitchen' => [
                'name' => 'Cold Kitchen',
                'description' => 'Salad and cold preparation station',
                'equipment' => 'Prep tables, refrigeration, slicers',
                'capacity' => 4,
                'specialties' => ['salads', 'cold_appetizers', 'desserts']
            ],
            'grill_station' => [
                'name' => 'Grill Station',
                'description' => 'Dedicated grilling station',
                'equipment' => 'Charcoal grill, gas grill, salamander',
                'capacity' => 6,
                'specialties' => ['grilled_meats', 'grilled_vegetables', 'seafood']
            ],
            'pastry_station' => [
                'name' => 'Pastry Station',
                'description' => 'Baking and dessert preparation',
                'equipment' => 'Ovens, mixers, decorating tools',
                'capacity' => 3,
                'specialties' => ['desserts', 'breads', 'pastries']
            ],
            'prep_station' => [
                'name' => 'Prep Station',
                'description' => 'Food preparation and mise en place',
                'equipment' => 'Cutting boards, knives, food processors',
                'capacity' => 6,
                'specialties' => ['prep_work', 'sauce_making', 'garnishes']
            ],
            'wash_station' => [
                'name' => 'Dish Washing Station',
                'description' => 'Dish and equipment cleaning',
                'equipment' => 'Dishwashers, sinks, sanitizers',
                'capacity' => 4,
                'specialties' => ['cleaning', 'sanitization']
            ],
            'expo_station' => [
                'name' => 'Expediting Station',
                'description' => 'Order assembly and quality check',
                'equipment' => 'Heat lamps, plating area, garnish station',
                'capacity' => 2,
                'specialties' => ['order_assembly', 'quality_control', 'presentation']
            ]
        ];

        foreach ($this->branches->take(3) as $branch) {
            $this->command->info("  🏪 Setting up stations for {$branch->name}");
            
            foreach ($stationTypes as $type => $config) {
                $station = KitchenStation::create([
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'branch_id' => $branch->id,
                    'organization_id' => $branch->organization_id,
                    'equipment' => $config['equipment'],
                    'max_capacity' => $config['capacity'],
                    'current_orders' => 0,
                    'is_active' => true,
                    'station_type' => $type,
                    'priority_level' => $type === 'expo_station' ? 1 : rand(2, 5),
                ]);
                
                $this->workflowData['stations_created'] = ($this->workflowData['stations_created'] ?? 0) + 1;
                
                // Assign staff to stations
                $this->assignStaffToStation($station, $branch);
            }
        }
        
        // Update kitchen stations collection
        $this->kitchenStations = KitchenStation::all();
    }

    private function assignStaffToStation(KitchenStation $station, Branch $branch): void
    {
        $branchStaff = $this->kitchenStaff->where('branch_id', $branch->id);
        
        $stationStaffMapping = [
            'hot_kitchen' => ['head_chef', 'sous_chef', 'line_cook'],
            'cold_kitchen' => ['salad_cook', 'prep_cook'],
            'grill_station' => ['grill_cook', 'line_cook'],
            'pastry_station' => ['pastry_chef'],
            'prep_station' => ['prep_cook', 'kitchen_assistant'],
            'wash_station' => ['kitchen_assistant'],
            'expo_station' => ['head_chef', 'sous_chef']
        ];
        
        $requiredRoles = $stationStaffMapping[$station->station_type] ?? ['line_cook'];
        $assignedStaff = [];
        
        foreach ($requiredRoles as $role) {
            $staffMember = $branchStaff->where('role', $role)->first();
            if ($staffMember) {
                $assignedStaff[] = $staffMember->id;
            }
        }
        
        // For demo purposes, we'll store this as JSON in a custom field
        // In a real system, you'd have a proper many-to-many relationship
        $station->update(['assigned_staff' => json_encode($assignedStaff)]);
    }

    private function seedBasicWorkflows(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  ⚙️ Creating basic workflows for {$branch->name}");
            
            $branchStations = $this->kitchenStations->where('branch_id', $branch->id);
            
            // 1. Standard Order Flow
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Standard Workflow Customer',
                'customer_phone' => '+94771111111',
                'order_type' => 'dine_in',
                'items' => [
                    ['name' => 'Grilled Chicken', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Caesar Salad', 'quantity' => 1, 'station' => 'cold_kitchen'],
                    ['name' => 'Chocolate Cake', 'quantity' => 1, 'station' => 'pastry_station']
                ],
                'workflow_type' => 'standard_flow'
            ]);
            
            // 2. Fast Track Workflow (Simple Items)
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Fast Track Customer',
                'customer_phone' => '+94772222222',
                'order_type' => 'takeaway',
                'items' => [
                    ['name' => 'Club Sandwich', 'quantity' => 2, 'station' => 'cold_kitchen'],
                    ['name' => 'Fresh Juice', 'quantity' => 2, 'station' => 'cold_kitchen']
                ],
                'workflow_type' => 'fast_track'
            ]);
            
            // 3. Complex Multi-Station Workflow
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Complex Order Customer',
                'customer_phone' => '+94773333333',
                'order_type' => 'dine_in',
                'items' => [
                    ['name' => 'Seafood Platter', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Cream Soup', 'quantity' => 1, 'station' => 'hot_kitchen'],
                    ['name' => 'Mixed Grill', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Garden Salad', 'quantity' => 1, 'station' => 'cold_kitchen'],
                    ['name' => 'Tiramisu', 'quantity' => 1, 'station' => 'pastry_station']
                ],
                'workflow_type' => 'complex_multi_station'
            ]);
        }
    }

    private function seedOrderProcessingScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  📋 Creating order processing scenarios for {$branch->name}");
            
            // 1. Priority Order Processing
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'VIP Customer',
                'customer_phone' => '+94774444444',
                'order_type' => 'dine_in',
                'priority' => 'high',
                'items' => [
                    ['name' => 'Premium Steak', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Lobster Bisque', 'quantity' => 1, 'station' => 'hot_kitchen']
                ],
                'workflow_type' => 'priority_processing'
            ]);
            
            // 2. Large Group Order
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Large Group Event',
                'customer_phone' => '+94775555555',
                'order_type' => 'dine_in',
                'items' => [
                    ['name' => 'Grilled Chicken', 'quantity' => 8, 'station' => 'grill_station'],
                    ['name' => 'Pasta Primavera', 'quantity' => 6, 'station' => 'hot_kitchen'],
                    ['name' => 'Caesar Salad', 'quantity' => 10, 'station' => 'cold_kitchen'],
                    ['name' => 'Chocolate Mousse', 'quantity' => 12, 'station' => 'pastry_station']
                ],
                'workflow_type' => 'large_group_order'
            ]);
            
            // 3. Rush Hour Simulation
            $rushOrders = [];
            for ($i = 1; $i <= 10; $i++) {
                $rushOrders[] = [
                    'customer_name' => "Rush Customer {$i}",
                    'customer_phone' => '+9477' . str_pad(6000000 + $i, 7, '0', STR_PAD_LEFT),
                    'order_type' => rand(0, 1) ? 'dine_in' : 'takeaway',
                    'items' => [
                        ['name' => 'Quick Bite ' . $i, 'quantity' => rand(1, 3), 'station' => 'hot_kitchen']
                    ],
                    'workflow_type' => 'rush_hour'
                ];
            }
            
            foreach ($rushOrders as $orderData) {
                $this->createOrderWithWorkflow($branch, $orderData);
            }
        }
    }

    private function seedStationCoordination(): void
    {
        foreach ($this->branches->take(1) as $branch) {
            $this->command->info("  🤝 Creating station coordination scenarios for {$branch->name}");
            
            // 1. Synchronized Course Service
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Course Timing Customer',
                'customer_phone' => '+94776666666',
                'order_type' => 'dine_in',
                'timing_requirements' => 'synchronized',
                'items' => [
                    ['name' => 'Appetizer Platter', 'quantity' => 1, 'station' => 'cold_kitchen', 'course' => 1],
                    ['name' => 'Main Course', 'quantity' => 2, 'station' => 'grill_station', 'course' => 2],
                    ['name' => 'Side Vegetables', 'quantity' => 2, 'station' => 'hot_kitchen', 'course' => 2],
                    ['name' => 'Dessert Special', 'quantity' => 2, 'station' => 'pastry_station', 'course' => 3]
                ],
                'workflow_type' => 'synchronized_courses'
            ]);
            
            // 2. Station Dependency Chain
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Dependency Chain Customer',
                'customer_phone' => '+94777777777',
                'order_type' => 'dine_in',
                'items' => [
                    ['name' => 'Fresh Herbs', 'quantity' => 1, 'station' => 'prep_station', 'dependency_order' => 1],
                    ['name' => 'Herb Crusted Fish', 'quantity' => 1, 'station' => 'grill_station', 'dependency_order' => 2],
                    ['name' => 'Garnish Preparation', 'quantity' => 1, 'station' => 'cold_kitchen', 'dependency_order' => 3],
                    ['name' => 'Final Plating', 'quantity' => 1, 'station' => 'expo_station', 'dependency_order' => 4]
                ],
                'workflow_type' => 'dependency_chain'
            ]);
            
            // 3. Resource Sharing Scenario
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Resource Sharing Customer',
                'customer_phone' => '+94778888888',
                'order_type' => 'dine_in',
                'shared_resources' => ['grill_space', 'oven_time', 'prep_staff'],
                'items' => [
                    ['name' => 'Mixed Grill Platter', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Baked Potato', 'quantity' => 1, 'station' => 'hot_kitchen'],
                    ['name' => 'Grilled Vegetables', 'quantity' => 1, 'station' => 'grill_station']
                ],
                'workflow_type' => 'resource_sharing'
            ]);
        }
    }

    private function seedPeakTimeScenarios(): void
    {
        foreach ($this->branches->take(2) as $branch) {
            $this->command->info("  🔥 Creating peak time scenarios for {$branch->name}");
            
            // 1. Weekend Rush Simulation
            $weekendOrders = [];
            for ($i = 1; $i <= 15; $i++) {
                $weekendOrders[] = [
                    'customer_name' => "Weekend Rush {$i}",
                    'customer_phone' => '+9477' . str_pad(8000000 + $i, 7, '0', STR_PAD_LEFT),
                    'order_type' => 'dine_in',
                    'rush_time' => 'weekend_peak',
                    'items' => [
                        ['name' => "Popular Item {$i}", 'quantity' => rand(1, 4), 'station' => ['hot_kitchen', 'grill_station'][rand(0, 1)]]
                    ],
                    'workflow_type' => 'weekend_rush'
                ];
            }
            
            foreach (array_slice($weekendOrders, 0, 8) as $orderData) {
                $this->createOrderWithWorkflow($branch, $orderData);
            }
            
            // 2. Holiday Special Event
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Holiday Special Event',
                'customer_phone' => '+94779999999',
                'order_type' => 'dine_in',
                'event_type' => 'holiday_special',
                'items' => [
                    ['name' => 'Holiday Turkey', 'quantity' => 3, 'station' => 'hot_kitchen'],
                    ['name' => 'Festive Sides', 'quantity' => 6, 'station' => 'hot_kitchen'],
                    ['name' => 'Holiday Dessert', 'quantity' => 4, 'station' => 'pastry_station']
                ],
                'workflow_type' => 'holiday_special'
            ]);
            
            // 3. Lunch Rush Hour
            $lunchRushOrders = [];
            for ($i = 1; $i <= 12; $i++) {
                $lunchRushOrders[] = [
                    'customer_name' => "Lunch Rush {$i}",
                    'customer_phone' => '+9477' . str_pad(9000000 + $i, 7, '0', STR_PAD_LEFT),
                    'order_type' => 'takeaway',
                    'timing_constraint' => 'express_lunch',
                    'items' => [
                        ['name' => "Express Lunch {$i}", 'quantity' => 1, 'station' => 'hot_kitchen']
                    ],
                    'workflow_type' => 'lunch_rush'
                ];
            }
            
            foreach (array_slice($lunchRushOrders, 0, 6) as $orderData) {
                $this->createOrderWithWorkflow($branch, $orderData);
            }
        }
    }

    private function seedSpecialItemWorkflows(): void
    {
        foreach ($this->branches->take(1) as $branch) {
            $this->command->info("  ✨ Creating special item workflows for {$branch->name}");
            
            // 1. Chef's Special Multi-Station Item
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Chef Special Customer',
                'customer_phone' => '+94771010101',
                'order_type' => 'dine_in',
                'special_requirements' => 'chef_signature',
                'items' => [
                    ['name' => 'Chef Signature Dish', 'quantity' => 1, 'station' => 'multiple', 'stations' => ['prep_station', 'grill_station', 'pastry_station', 'expo_station']]
                ],
                'workflow_type' => 'chef_special'
            ]);
            
            // 2. Dietary Restriction Workflow
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Dietary Restriction Customer',
                'customer_phone' => '+94772020202',
                'order_type' => 'dine_in',
                'dietary_restrictions' => ['gluten_free', 'vegan', 'nut_allergy'],
                'items' => [
                    ['name' => 'Gluten-Free Pasta', 'quantity' => 1, 'station' => 'hot_kitchen'],
                    ['name' => 'Vegan Dessert', 'quantity' => 1, 'station' => 'pastry_station']
                ],
                'workflow_type' => 'dietary_restriction'
            ]);
            
            // 3. Custom Preparation Request
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Custom Request Customer',
                'customer_phone' => '+94773030303',
                'order_type' => 'dine_in',
                'custom_requests' => ['extra_spicy', 'no_onions', 'sauce_on_side'],
                'items' => [
                    ['name' => 'Custom Spicy Burger', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Modified Salad', 'quantity' => 1, 'station' => 'cold_kitchen']
                ],
                'workflow_type' => 'custom_preparation'
            ]);
            
            // 4. Temperature-Critical Items
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Temperature Critical Customer',
                'customer_phone' => '+94774040404',
                'order_type' => 'dine_in',
                'temperature_requirements' => 'precision_cooking',
                'items' => [
                    ['name' => 'Medium-Rare Steak', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Soufflé', 'quantity' => 1, 'station' => 'pastry_station']
                ],
                'workflow_type' => 'temperature_critical'
            ]);
        }
    }

    private function seedEmergencyScenarios(): void
    {
        foreach ($this->branches->take(1) as $branch) {
            $this->command->info("  🚨 Creating emergency scenarios for {$branch->name}");
            
            // 1. Equipment Failure Recovery
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Equipment Failure Customer',
                'customer_phone' => '+94775050505',
                'order_type' => 'dine_in',
                'emergency_type' => 'equipment_failure',
                'affected_station' => 'grill_station',
                'items' => [
                    ['name' => 'Alternative Grilled Item', 'quantity' => 1, 'station' => 'hot_kitchen'] // Moved from grill to hot kitchen
                ],
                'workflow_type' => 'equipment_failure'
            ]);
            
            // 2. Staff Shortage Adaptation
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Staff Shortage Customer',
                'customer_phone' => '+94776060606',
                'order_type' => 'dine_in',
                'emergency_type' => 'staff_shortage',
                'affected_positions' => ['pastry_chef'],
                'items' => [
                    ['name' => 'Simple Dessert Alternative', 'quantity' => 1, 'station' => 'cold_kitchen'] // Moved from pastry to cold
                ],
                'workflow_type' => 'staff_shortage'
            ]);
            
            // 3. Ingredient Shortage Substitution
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Ingredient Shortage Customer',
                'customer_phone' => '+94777070707',
                'order_type' => 'dine_in',
                'emergency_type' => 'ingredient_shortage',
                'missing_ingredients' => ['salmon', 'specific_cheese'],
                'items' => [
                    ['name' => 'Substitute Fish Dish', 'quantity' => 1, 'station' => 'grill_station'],
                    ['name' => 'Alternative Cheese Dish', 'quantity' => 1, 'station' => 'hot_kitchen']
                ],
                'workflow_type' => 'ingredient_shortage'
            ]);
            
            // 4. Power Outage Backup Procedures
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Power Outage Customer',
                'customer_phone' => '+94778080808',
                'order_type' => 'takeaway',
                'emergency_type' => 'power_outage',
                'backup_procedures' => 'cold_items_only',
                'items' => [
                    ['name' => 'Cold Sandwich', 'quantity' => 2, 'station' => 'cold_kitchen'],
                    ['name' => 'Pre-made Salad', 'quantity' => 1, 'station' => 'cold_kitchen']
                ],
                'workflow_type' => 'power_outage'
            ]);
        }
    }

    private function seedQualityControlWorkflows(): void
    {
        foreach ($this->branches->take(1) as $branch) {
            $this->command->info("  ✅ Creating quality control workflows for {$branch->name}");
            
            // 1. Quality Check Points
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Quality Control Customer',
                'customer_phone' => '+94779090909',
                'order_type' => 'dine_in',
                'quality_checkpoints' => ['prep_check', 'cooking_check', 'presentation_check'],
                'items' => [
                    ['name' => 'Premium Quality Dish', 'quantity' => 1, 'station' => 'grill_station']
                ],
                'workflow_type' => 'quality_control'
            ]);
            
            // 2. Food Safety Protocol
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Food Safety Customer',
                'customer_phone' => '+94771111111',
                'order_type' => 'dine_in',
                'safety_protocols' => ['temperature_check', 'allergen_verification', 'hygiene_standards'],
                'items' => [
                    ['name' => 'Food Safety Verified Dish', 'quantity' => 1, 'station' => 'hot_kitchen']
                ],
                'workflow_type' => 'food_safety'
            ]);
            
            // 3. Presentation Standards
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Presentation Standards Customer',
                'customer_phone' => '+94772222222',
                'order_type' => 'dine_in',
                'presentation_level' => 'premium',
                'items' => [
                    ['name' => 'Signature Presentation Dish', 'quantity' => 1, 'station' => 'expo_station']
                ],
                'workflow_type' => 'presentation_standards'
            ]);
        }
    }

    private function seedCrossTrainingScenarios(): void
    {
        foreach ($this->branches->take(1) as $branch) {
            $this->command->info("  🎓 Creating cross-training scenarios for {$branch->name}");
            
            // 1. Cross-Station Flexibility
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Cross Training Customer',
                'customer_phone' => '+94773333333',
                'order_type' => 'dine_in',
                'cross_training' => ['grill_to_hot', 'cold_to_prep'],
                'items' => [
                    ['name' => 'Flexible Station Dish', 'quantity' => 1, 'station' => 'multiple']
                ],
                'workflow_type' => 'cross_training'
            ]);
            
            // 2. Multi-Skill Utilization
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Multi Skill Customer',
                'customer_phone' => '+94774444444',
                'order_type' => 'dine_in',
                'multi_skills' => ['chef_serves_as_prep', 'pastry_assists_grill'],
                'items' => [
                    ['name' => 'Multi Skill Required Dish', 'quantity' => 1, 'station' => 'hot_kitchen']
                ],
                'workflow_type' => 'multi_skill'
            ]);
        }
    }

    private function seedPerformanceScenarios(): void
    {
        foreach ($this->branches->take(1) as $branch) {
            $this->command->info("  📈 Creating performance scenarios for {$branch->name}");
            
            // 1. Efficiency Optimization
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Efficiency Test Customer',
                'customer_phone' => '+94775555555',
                'order_type' => 'dine_in',
                'efficiency_metrics' => ['prep_time', 'cook_time', 'plate_time'],
                'items' => [
                    ['name' => 'Efficiency Test Dish', 'quantity' => 1, 'station' => 'hot_kitchen']
                ],
                'workflow_type' => 'efficiency_optimization'
            ]);
            
            // 2. Waste Reduction Protocol
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Waste Reduction Customer',
                'customer_phone' => '+94776666666',
                'order_type' => 'dine_in',
                'waste_reduction' => ['portion_control', 'ingredient_optimization'],
                'items' => [
                    ['name' => 'Waste Optimized Dish', 'quantity' => 1, 'station' => 'grill_station']
                ],
                'workflow_type' => 'waste_reduction'
            ]);
            
            // 3. Cost Control Measures
            $this->createOrderWithWorkflow($branch, [
                'customer_name' => 'Cost Control Customer',
                'customer_phone' => '+94777777777',
                'order_type' => 'dine_in',
                'cost_controls' => ['ingredient_substitution', 'portion_standardization'],
                'items' => [
                    ['name' => 'Cost Controlled Dish', 'quantity' => 1, 'station' => 'hot_kitchen']
                ],
                'workflow_type' => 'cost_control'
            ]);
        }
    }

    private function createOrderWithWorkflow(Branch $branch, array $orderData): Order
    {
        // Create the order
        $order = Order::create([
            'customer_name' => $orderData['customer_name'],
            'customer_phone' => $orderData['customer_phone'],
            'branch_id' => $branch->id,
            'organization_id' => $branch->organization_id,
            'order_type' => $orderData['order_type'],
            'status' => 'submitted',
            'order_time' => now(),
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'workflow_type' => $orderData['workflow_type'] ?? 'standard',
            'special_instructions' => json_encode([
                'priority' => $orderData['priority'] ?? 'normal',
                'timing_requirements' => $orderData['timing_requirements'] ?? null,
                'dietary_restrictions' => $orderData['dietary_restrictions'] ?? [],
                'custom_requests' => $orderData['custom_requests'] ?? [],
                'emergency_type' => $orderData['emergency_type'] ?? null,
                'quality_checkpoints' => $orderData['quality_checkpoints'] ?? [],
                'cross_training' => $orderData['cross_training'] ?? [],
                'efficiency_metrics' => $orderData['efficiency_metrics'] ?? []
            ])
        ]);
        
        $subtotal = 0;
        
        // Create order items with station assignments
        foreach ($orderData['items'] as $itemData) {
            $station = $this->getStationForItem($branch, $itemData['station']);
            
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => 1, // Placeholder - in real system would map to actual menu items
                'inventory_item_id' => 1, // Placeholder
                'quantity' => $itemData['quantity'],
                'unit_price' => 15.00, // Demo price
                'total_price' => $itemData['quantity'] * 15.00,
                'assigned_station_id' => $station?->id,
                'status' => 'pending',
                'prep_time_estimate' => rand(5, 30),
                'cook_time_estimate' => rand(10, 45),
                'course' => $itemData['course'] ?? 1,
                'dependency_order' => $itemData['dependency_order'] ?? 1,
                'special_instructions' => json_encode([
                    'station_requirements' => $itemData['stations'] ?? [],
                    'temperature_requirements' => $orderData['temperature_requirements'] ?? null,
                    'custom_requests' => $orderData['custom_requests'] ?? []
                ])
            ]);
            
            $subtotal += $orderItem->total_price;
        }
        
        // Update order totals
        $tax = $subtotal * 0.10;
        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax
        ]);
        
        // Track workflow data
        $workflowType = $orderData['workflow_type'];
        $this->workflowData[$workflowType] = ($this->workflowData[$workflowType] ?? 0) + 1;
        
        return $order;
    }

    private function getStationForItem(Branch $branch, string $stationType): ?KitchenStation
    {
        if ($stationType === 'multiple') {
            return $this->kitchenStations->where('branch_id', $branch->id)->first();
        }
        
        return $this->kitchenStations
            ->where('branch_id', $branch->id)
            ->where('station_type', $stationType)
            ->first();
    }

    private function displayKitchenSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 EXHAUSTIVE KITCHEN WORKFLOW SEEDING SUMMARY');
        $this->command->info('══════════════════════════════════════════════════════════');
        
        $totalStations = KitchenStation::count();
        $totalOrders = Order::count();
        $totalOrderItems = OrderItem::count();
        
        $this->command->info("🏪 Total Kitchen Stations: {$totalStations}");
        $this->command->info("📋 Total Orders Created: {$totalOrders}");
        $this->command->info("🍽️ Total Order Items: {$totalOrderItems}");
        
        $this->command->newLine();
        $this->command->info('🎯 WORKFLOW SCENARIO BREAKDOWN:');
        
        foreach ($this->workflowData as $workflow => $count) {
            $workflowName = ucwords(str_replace('_', ' ', $workflow));
            $this->command->info(sprintf('  %-30s: %d scenarios', $workflowName, $count));
        }
        
        $this->command->newLine();
        $this->command->info('🏪 STATION DISTRIBUTION:');
        
        $stationCounts = KitchenStation::selectRaw('station_type, COUNT(*) as count')
            ->groupBy('station_type')
            ->pluck('count', 'station_type');
            
        foreach ($stationCounts as $type => $count) {
            $typeName = ucwords(str_replace('_', ' ', $type));
            $this->command->info(sprintf('  %-25s: %d stations', $typeName, $count));
        }
        
        $this->command->newLine();
        $this->command->info('📈 ORDER STATUS DISTRIBUTION:');
        
        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
            
        foreach ($statusCounts as $status => $count) {
            $statusName = ucfirst($status);
            $this->command->info(sprintf('  %-15s: %d orders', $statusName, $count));
        }
        
        $this->command->newLine();
        $this->command->info('✅ All kitchen workflow scenarios have been comprehensively seeded!');
        $this->command->info('🔍 Scenarios include: basic workflows, order processing, station coordination,');
        $this->command->info('    peak time management, special items, emergency procedures, quality control,');
        $this->command->info('    cross-training flexibility, and performance optimization workflows.');
    }
}
