<?php
/**
 * Verify Admin Sidebar Updates - Unified Order Flow
 * 
 * This script verifies that the admin sidebar has been properly updated
 * to reflect the new unified order flow.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Admin Sidebar Updates Verification\n";
echo "=====================================\n\n";

$checks = [];
$passed = 0;
$total = 0;

// Helper function to check if a string exists in a file
function checkFileContains($filePath, $searchString, $description) {
    global $checks, $passed, $total;
    $total++;
    
    if (!file_exists($filePath)) {
        $checks[] = "❌ $description - File not found: $filePath";
        return false;
    }
    
    $content = file_get_contents($filePath);
    $exists = strpos($content, $searchString) !== false;
    
    if ($exists) {
        $checks[] = "✅ $description";
        $passed++;
        return true;
    } else {
        $checks[] = "❌ $description - String not found: '$searchString'";
        return false;
    }
}

// Helper function to check if a string does NOT exist in a file
function checkFileNotContains($filePath, $searchString, $description) {
    global $checks, $passed, $total;
    $total++;
    
    if (!file_exists($filePath)) {
        $checks[] = "❌ $description - File not found: $filePath";
        return false;
    }
    
    $content = file_get_contents($filePath);
    $exists = strpos($content, $searchString) !== false;
    
    if (!$exists) {
        $checks[] = "✅ $description";
        $passed++;
        return true;
    } else {
        $checks[] = "❌ $description - String should not exist: '$searchString'";
        return false;
    }
}

// 1. Check AdminSidebar.php has been updated
echo "📋 Checking AdminSidebar Component Updates...\n";

// Check that Create Order is now included in order sub-items
checkFileContains(
    'app/View/Components/AdminSidebar.php',
    "'title' => 'Create Order'",
    "Create Order menu item added to sidebar"
);

// Check that takeaway orders now filter to main index
checkFileContains(
    'app/View/Components/AdminSidebar.php',
    "'route_params' => ['type' => 'takeaway']",
    "Takeaway Orders filter properly configured"
);

// Check that dine-in orders filter is added
checkFileContains(
    'app/View/Components/AdminSidebar.php',
    "'route_params' => ['type' => 'in_house']",
    "Dine-In Orders filter properly configured"
);

// Check that legacy takeaway.index route is no longer referenced
checkFileNotContains(
    'app/View/Components/AdminSidebar.php',
    "admin.orders.takeaway.index",
    "Legacy takeaway.index route removed from sidebar"
);

// 2. Check AdminOrderController updates
echo "\n🎮 Checking AdminOrderController Updates...\n";

// Check that indexTakeaway now redirects
checkFileContains(
    'app/Http/Controllers/AdminOrderController.php',
    "return redirect()->route('admin.orders.index', ['type' => 'takeaway']);",
    "indexTakeaway method redirects to unified index"
);

// Check that type parameter handling is added
checkFileContains(
    'app/Http/Controllers/AdminOrderController.php',
    "if (\$request->filled('type')) {",
    "Type parameter handling added to main index method"
);

// 3. Check orders index view updates  
echo "\n📄 Checking Orders Index View Updates...\n";

// Check that title reflects current filter
checkFileContains(
    'resources/views/admin/orders/index.blade.php',
    "Takeaway Orders",
    "Dynamic title for takeaway orders"
);

checkFileContains(
    'resources/views/admin/orders/index.blade.php',
    "Dine-In Orders", 
    "Dynamic title for dine-in orders"
);

// Check that Create Order button is unified
checkFileContains(
    'resources/views/admin/orders/index.blade.php',
    "Create Order",
    "Unified Create Order button"
);

// Check that legacy Create Takeaway button is removed
checkFileNotContains(
    'resources/views/admin/orders/index.blade.php',
    "Create Takeaway",
    "Legacy Create Takeaway button removed"
);

// 4. Route verification
echo "\n🛣️ Checking Route Configuration...\n";

try {
    // Check if routes are properly configured
    $allRoutes = Illuminate\Support\Facades\Route::getRoutes();
    
    $adminOrdersCreate = null;
    $adminOrdersTakeawayIndex = null;
    
    foreach ($allRoutes as $route) {
        if ($route->getName() === 'admin.orders.create') {
            $adminOrdersCreate = $route;
        }
        if ($route->getName() === 'admin.orders.takeaway.index') {
            $adminOrdersTakeawayIndex = $route;
        }
    }
    
    if ($adminOrdersCreate) {
        $checks[] = "✅ admin.orders.create route exists";
        $passed++;
    } else {
        $checks[] = "❌ admin.orders.create route missing";
    }
    $total++;
    
    if ($adminOrdersTakeawayIndex) {
        $checks[] = "✅ admin.orders.takeaway.index route exists (for backward compatibility)";
        $passed++;
    } else {
        $checks[] = "❌ admin.orders.takeaway.index route missing";
    }
    $total++;
    
} catch (Exception $e) {
    $checks[] = "❌ Route verification failed: " . $e->getMessage();
    $total += 2;
}

// 5. Check that unified create form handles both order types
echo "\n📝 Checking Unified Create Form...\n";

checkFileContains(
    'resources/views/admin/orders/create.blade.php',
    'name="order_type" value="in_house"',
    "Create form supports dine-in orders"
);

checkFileContains(
    'resources/views/admin/orders/create.blade.php',
    'name="order_type" value="takeaway"',
    "Create form supports takeaway orders"
);

// Display results
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 VERIFICATION RESULTS\n";
echo str_repeat("=", 50) . "\n";

foreach ($checks as $check) {
    echo "$check\n";
}

echo "\n" . str_repeat("-", 50) . "\n";
echo "🎯 SUMMARY: $passed/$total checks passed\n";

if ($passed === $total) {
    echo "🎉 SUCCESS: Admin sidebar has been successfully updated for unified order flow!\n";
    echo "\n✨ Key Changes Made:\n";
    echo "   • Removed legacy 'Takeaway Orders' link that pointed to separate view\n";
    echo "   • Added unified 'Create Order' that handles both dine-in and takeaway\n";
    echo "   • Updated menu items to use filters instead of separate routes\n";
    echo "   • Maintained backward compatibility for existing takeaway routes\n";
    echo "   • Updated order index view to show dynamic titles based on filters\n";
    exit(0);
} else {
    echo "⚠️  WARNING: Some checks failed. Review the issues above.\n";
    exit(1);
}
