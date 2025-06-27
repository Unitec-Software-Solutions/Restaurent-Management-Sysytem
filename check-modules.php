<?php

// Check modules table
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking modules table...\n\n";

try {
    $count = App\Models\Module::count();
    echo "Total modules: {$count}\n";
    
    if ($count > 0) {
        $modules = App\Models\Module::active()->get(['id', 'name', 'is_active']);
        echo "Active modules:\n";
        foreach ($modules as $module) {
            echo "- {$module->name} (ID: {$module->id})\n";
        }
    } else {
        echo "No modules found - creating sample modules...\n";
        
        // Create sample modules
        $sampleModules = [
            ['name' => 'Restaurant Management', 'slug' => 'restaurant-management', 'description' => 'Core restaurant management features', 'permissions' => ['restaurant.view', 'restaurant.manage'], 'is_active' => true],
            ['name' => 'Order Management', 'slug' => 'order-management', 'description' => 'Manage orders and takeaways', 'permissions' => ['orders.view', 'orders.create', 'orders.update'], 'is_active' => true],
            ['name' => 'Inventory Management', 'slug' => 'inventory-management', 'description' => 'Track inventory and stock', 'permissions' => ['inventory.view', 'inventory.manage'], 'is_active' => true],
            ['name' => 'Reservation System', 'slug' => 'reservation-system', 'description' => 'Table reservations and booking', 'permissions' => ['reservations.view', 'reservations.manage'], 'is_active' => true],
            ['name' => 'Payment Processing', 'slug' => 'payment-processing', 'description' => 'Payment gateway integration', 'permissions' => ['payments.view', 'payments.process'], 'is_active' => true],
            ['name' => 'Analytics & Reporting', 'slug' => 'analytics-reporting', 'description' => 'Business intelligence and reports', 'permissions' => ['analytics.view', 'reports.generate'], 'is_active' => true],
            ['name' => 'Staff Management', 'slug' => 'staff-management', 'description' => 'Employee and role management', 'permissions' => ['staff.view', 'staff.manage'], 'is_active' => true],
            ['name' => 'Multi-Branch Support', 'slug' => 'multi-branch', 'description' => 'Support for multiple locations', 'permissions' => ['branches.view', 'branches.manage'], 'is_active' => true]
        ];
        
        foreach ($sampleModules as $moduleData) {
            App\Models\Module::create($moduleData);
            echo "✅ Created module: {$moduleData['name']}\n";
        }
        
        echo "\n✅ Sample modules created successfully!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
