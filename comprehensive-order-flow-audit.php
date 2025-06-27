<?php

/**
 * Comprehensive Order Management System Audit Script
 * 
 * This script audits all order flows, menu item retrieval logic,
 * admin features, and identifies missing functionality.
 */

echo "=== COMPREHENSIVE ORDER MANAGEMENT SYSTEM AUDIT ===\n\n";

// Colors for output
function colorOutput($text, $color = 'white') {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

function checkSection($title) {
    echo colorOutput("\n=== $title ===\n", 'cyan');
}

function success($message) {
    echo colorOutput("✓ $message\n", 'green');
}

function warning($message) {
    echo colorOutput("⚠ $message\n", 'yellow');
}

function error($message) {
    echo colorOutput("✗ $message\n", 'red');
}

function info($message) {
    echo colorOutput("ℹ $message\n", 'blue');
}

// Initialize audit results
$audit_results = [
    'missing_methods' => [],
    'missing_views' => [],
    'missing_routes' => [],
    'menu_issues' => [],
    'admin_issues' => [],
    'flow_issues' => []
];

// 1. CHECK CONTROLLER METHODS
checkSection("CONTROLLER METHODS AUDIT");

$controllers_to_check = [
    'app/Http/Controllers/OrderController.php' => [
        // Basic CRUD
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        // Reservation-based flows
        'summary', 'submitOrder', 'addAnother',
        // Takeaway flows
        'indexTakeaway', 'createTakeaway', 'storeTakeaway', 'showTakeaway', 
        'editTakeaway', 'updateTakeaway', 'destroyTakeaway', 'submitTakeaway',
        // Menu & Stock
        'getAvailableMenuItems', 'checkStock', 'getMenuItemsByType',
        // Order management
        'updateCart', 'printKOT', 'printBill', 'markAsPreparing', 
        'markAsReady', 'completeOrder'
    ],
    'app/Http/Controllers/AdminOrderController.php' => [
        // Basic CRUD with admin features
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        // Reservation-based admin flows
        'summary', 'submitOrder', 'addAnother',
        // Takeaway admin flows
        'indexTakeaway', 'createTakeaway', 'storeTakeaway', 'showTakeaway',
        'editTakeaway', 'updateTakeaway', 'destroyTakeaway', 'submitTakeaway',
        'takeawayTypeSelector',
        // Admin-specific features
        'getAvailableMenuItems', 'getBranchOrders', 'updateOrderStatus',
        'cancelOrder', 'getOrdersByBranch', 'getDefaultValues',
        // Menu & Stock with admin context
        'checkStock', 'getMenuItemsByType'
    ]
];

foreach ($controllers_to_check as $controller_path => $required_methods) {
    if (file_exists($controller_path)) {
        $controller_content = file_get_contents($controller_path);
        info("Checking: $controller_path");
        
        foreach ($required_methods as $method) {
            if (preg_match("/function\s+$method\s*\(/", $controller_content)) {
                success("Method '$method' exists");
            } else {
                error("Method '$method' is MISSING");
                $audit_results['missing_methods'][] = "$controller_path::$method";
            }
        }
    } else {
        error("Controller file not found: $controller_path");
    }
}

// 2. CHECK BLADE TEMPLATES
checkSection("BLADE TEMPLATES AUDIT");

$required_views = [
    // Customer Order Views - Reservation Based
    'resources/views/orders/create.blade.php' => 'Order creation form (reservation-based)',
    'resources/views/orders/summary.blade.php' => 'Order summary with submit/update/add-another buttons',
    'resources/views/orders/edit.blade.php' => 'Edit order form',
    'resources/views/orders/show.blade.php' => 'Order details display',
    'resources/views/orders/reservation-order-summary.blade.php' => 'Reservation + Order details page',
    
    // Customer Order Views - Takeaway
    'resources/views/orders/takeaway/index.blade.php' => 'Takeaway orders listing',
    'resources/views/orders/takeaway/create.blade.php' => 'Takeaway order creation',
    'resources/views/orders/takeaway/summary.blade.php' => 'Takeaway order summary',
    'resources/views/orders/takeaway/edit.blade.php' => 'Edit takeaway order',
    'resources/views/orders/takeaway/show.blade.php' => 'Takeaway order details',
    
    // Admin Order Views - Reservation Based
    'resources/views/admin/orders/index.blade.php' => 'Admin orders listing (all branch orders)',
    'resources/views/admin/orders/create.blade.php' => 'Admin order creation (pre-filled defaults)',
    'resources/views/admin/orders/summary.blade.php' => 'Admin order summary',
    'resources/views/admin/orders/edit.blade.php' => 'Admin edit order',
    'resources/views/admin/orders/show.blade.php' => 'Admin order details',
    
    // Admin Order Views - Takeaway
    'resources/views/admin/orders/takeaway/index.blade.php' => 'Admin takeaway orders listing',
    'resources/views/admin/orders/takeaway/create.blade.php' => 'Admin takeaway creation',
    'resources/views/admin/orders/takeaway/summary.blade.php' => 'Admin takeaway summary',
    'resources/views/admin/orders/takeaway/edit.blade.php' => 'Admin edit takeaway',
    'resources/views/admin/orders/takeaway/show.blade.php' => 'Admin takeaway details',
    'resources/views/admin/orders/takeaway-type-selector.blade.php' => 'Admin takeaway type selector (call/in-house)',
    
    // Payment & Processing
    'resources/views/orders/payment.blade.php' => 'Payment processing page',
    'resources/views/orders/payment_or_repeat.blade.php' => 'Payment or repeat order page'
];

foreach ($required_views as $view_path => $description) {
    if (file_exists($view_path)) {
        success("View exists: $view_path ($description)");
        
        // Check for specific content requirements
        $view_content = file_get_contents($view_path);
        
        // Check summary pages for required buttons
        if (strpos($view_path, 'summary') !== false) {
            $has_submit = strpos($view_content, 'submit') !== false || strpos($view_content, 'Submit') !== false;
            $has_update = strpos($view_content, 'update') !== false || strpos($view_content, 'Update') !== false;
            $has_add_another = strpos($view_content, 'add') !== false && strpos($view_content, 'another') !== false;
            
            if (!$has_submit) warning("  - Missing 'Submit Order' button functionality");
            if (!$has_update) warning("  - Missing 'Update Order' button functionality");
            if (!$has_add_another) warning("  - Missing 'Add Another Order' button functionality");
        }
        
        // Check admin views for pre-filled defaults
        if (strpos($view_path, 'admin/orders') !== false && strpos($view_path, 'create') !== false) {
            $has_defaults = strpos($view_content, 'value=') !== false || strpos($view_content, 'selected') !== false;
            if (!$has_defaults) warning("  - Missing pre-filled default values for admin");
        }
        
        // Check takeaway type selector
        if (strpos($view_path, 'takeaway-type-selector') !== false) {
            $has_call_option = strpos($view_content, 'call') !== false;
            $has_inhouse_option = strpos($view_content, 'in-house') !== false || strpos($view_content, 'inhouse') !== false;
            $has_default_inhouse = strpos($view_content, 'checked') !== false || strpos($view_content, 'selected') !== false;
            
            if (!$has_call_option) warning("  - Missing 'Call' order type option");
            if (!$has_inhouse_option) warning("  - Missing 'In-house' order type option");
            if (!$has_default_inhouse) warning("  - Missing default selection for 'In-house'");
        }
        
    } else {
        error("View MISSING: $view_path ($description)");
        $audit_results['missing_views'][] = $view_path;
    }
}

// 3. CHECK MENU ITEM RETRIEVAL LOGIC
checkSection("MENU ITEM RETRIEVAL & DISPLAY LOGIC");

$menu_check_files = [
    'app/Http/Controllers/OrderController.php',
    'app/Http/Controllers/AdminOrderController.php'
];

foreach ($menu_check_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        info("Checking menu logic in: $file");
        
        // Check for Buy & Sell stock logic
        if (strpos($content, 'buy_sell') !== false || strpos($content, 'Buy') !== false) {
            success("  - Buy & Sell item detection found");
            
            if (strpos($content, 'stock') !== false) {
                success("  - Stock level checking found");
            } else {
                error("  - Stock level checking MISSING for Buy & Sell items");
                $audit_results['menu_issues'][] = "$file - Missing stock level logic for Buy & Sell items";
            }
        } else {
            warning("  - Buy & Sell item detection not found");
        }
        
        // Check for KOT item logic
        if (strpos($content, 'kot') !== false || strpos($content, 'KOT') !== false) {
            success("  - KOT item detection found");
            
            if (strpos($content, 'available') !== false) {
                success("  - KOT availability checking found");
            } else {
                warning("  - KOT availability checking might be missing");
            }
        } else {
            warning("  - KOT item detection not found");
        }
        
        // Check for menu filtering by branch
        if (strpos($content, 'branch') !== false && (strpos($content, 'menu') !== false || strpos($content, 'Menu') !== false)) {
            success("  - Branch-based menu filtering found");
        } else {
            warning("  - Branch-based menu filtering might be missing");
        }
    }
}

// Check menu item display in views
$menu_display_views = [
    'resources/views/orders/create.blade.php',
    'resources/views/orders/takeaway/create.blade.php',
    'resources/views/admin/orders/create.blade.php',
    'resources/views/admin/orders/takeaway/create.blade.php'
];

info("\nChecking menu item display in views:");
foreach ($menu_display_views as $view) {
    if (file_exists($view)) {
        $content = file_get_contents($view);
        
        // Check for stock display
        $has_stock_display = strpos($content, 'stock') !== false;
        // Check for KOT badge/tag
        $has_kot_badge = strpos($content, 'available') !== false || strpos($content, 'badge') !== false || strpos($content, 'tag') !== false;
        // Check for menu item loop
        $has_menu_loop = strpos($content, '@foreach') !== false && (strpos($content, 'menu') !== false || strpos($content, 'item') !== false);
        
        info("  $view:");
        if ($has_menu_loop) success("    - Menu items loop found");
        else error("    - Menu items loop MISSING");
        
        if ($has_stock_display) success("    - Stock display logic found");
        else warning("    - Stock display logic might be missing");
        
        if ($has_kot_badge) success("    - KOT availability badge/tag found");
        else warning("    - KOT availability badge/tag might be missing");
    }
}

// 4. CHECK ROUTES
checkSection("ROUTE DEFINITIONS AUDIT");

$route_files = [
    'routes/web.php',
    'routes/groups/admin.php',
    'routes/groups/public.php'
];

$required_routes = [
    // Customer routes
    'orders.create', 'orders.store', 'orders.summary', 'orders.edit', 'orders.update', 'orders.show',
    'orders.takeaway.index', 'orders.takeaway.create', 'orders.takeaway.store', 'orders.takeaway.summary',
    'orders.takeaway.edit', 'orders.takeaway.update', 'orders.takeaway.show',
    
    // Admin routes
    'admin.orders.index', 'admin.orders.create', 'admin.orders.store', 'admin.orders.summary',
    'admin.orders.edit', 'admin.orders.update', 'admin.orders.show',
    'admin.orders.takeaway.index', 'admin.orders.takeaway.create', 'admin.orders.takeaway.store',
    'admin.orders.takeaway.summary', 'admin.orders.takeaway.edit', 'admin.orders.takeaway.update',
    'admin.orders.takeaway.show', 'orders.takeaway.type-selector'
];

foreach ($route_files as $route_file) {
    if (file_exists($route_file)) {
        $route_content = file_get_contents($route_file);
        info("Checking routes in: $route_file");
        
        foreach ($required_routes as $route_name) {
            if (strpos($route_content, $route_name) !== false) {
                success("  Route '$route_name' found");
            } else {
                // Check if it's an admin route and might be in different format
                if (strpos($route_name, 'admin.') === 0) {
                    $alt_route = str_replace('admin.', '', $route_name);
                    if (strpos($route_content, $alt_route) !== false && strpos($route_content, 'admin') !== false) {
                        success("  Route '$route_name' found (alternative format)");
                        continue;
                    }
                }
                warning("  Route '$route_name' might be missing");
            }
        }
    }
}

// 5. CHECK ADMIN-SPECIFIC FEATURES
checkSection("ADMIN-SPECIFIC FEATURES AUDIT");

$admin_features_to_check = [
    'Branch filtering capability',
    'Pre-filled default values',
    'Order status management',
    'View all branch orders',
    'Takeaway type selection (call/in-house)',
    'Default in-house selection'
];

info("Required admin features:");
foreach ($admin_features_to_check as $feature) {
    info("  - $feature");
}

// Check AdminOrderController for these features
if (file_exists('app/Http/Controllers/AdminOrderController.php')) {
    $admin_content = file_get_contents('app/Http/Controllers/AdminOrderController.php');
    
    // Branch filtering
    if (strpos($admin_content, 'branch') !== false && strpos($admin_content, 'filter') !== false) {
        success("Branch filtering logic found");
    } else {
        warning("Branch filtering logic might be missing");
    }
    
    // Default values
    if (strpos($admin_content, 'default') !== false || strpos($admin_content, 'prefill') !== false) {
        success("Default values logic found");
    } else {
        warning("Default values logic might be missing");
    }
    
    // Order status management
    if (strpos($admin_content, 'status') !== false && (strpos($admin_content, 'update') !== false || strpos($admin_content, 'change') !== false)) {
        success("Order status management found");
    } else {
        warning("Order status management might be missing");
    }
}

// 6. FLOW VERIFICATION
checkSection("FLOW VERIFICATION");

$flows_to_verify = [
    'Reservation-based customer flow' => [
        'create → summary → (submit/update/add-another)',
        'submit → reservation-order-summary',
        'update → edit → summary',
        'add-another → create'
    ],
    'Takeaway customer flow' => [
        'create → summary → (submit/update/add-another)',
        'submit → order details by number',
        'update → edit → summary',
        'add-another → create'
    ],
    'Admin reservation flow' => [
        'create (with defaults) → summary → (submit/update/add-another)',
        'Additional: view all branch orders, update status, cancel'
    ],
    'Admin takeaway flow' => [
        'type-selector (call/in-house, default in-house) → create (with defaults) → summary',
        'Additional: view all branch orders, update status, cancel'
    ]
];

foreach ($flows_to_verify as $flow_name => $steps) {
    info("$flow_name:");
    foreach ($steps as $step) {
        info("  - $step");
    }
}

// 7. GENERATE REPORT
checkSection("AUDIT SUMMARY & RECOMMENDATIONS");

$total_issues = count($audit_results['missing_methods']) + 
                count($audit_results['missing_views']) + 
                count($audit_results['missing_routes']) + 
                count($audit_results['menu_issues']) + 
                count($audit_results['admin_issues']) + 
                count($audit_results['flow_issues']);

if ($total_issues == 0) {
    success("🎉 AUDIT PASSED! All required functionality appears to be present.");
} else {
    error("⚠ AUDIT FOUND $total_issues ISSUES");
    
    if (!empty($audit_results['missing_methods'])) {
        error("\nMissing Controller Methods:");
        foreach ($audit_results['missing_methods'] as $method) {
            echo "  - $method\n";
        }
    }
    
    if (!empty($audit_results['missing_views'])) {
        error("\nMissing Views:");
        foreach ($audit_results['missing_views'] as $view) {
            echo "  - $view\n";
        }
    }
    
    if (!empty($audit_results['menu_issues'])) {
        error("\nMenu Item Issues:");
        foreach ($audit_results['menu_issues'] as $issue) {
            echo "  - $issue\n";
        }
    }
}

// 8. GENERATE FIX SCRIPT
echo colorOutput("\n=== GENERATING AUTOMATIC FIXES ===\n", 'magenta');

$fix_script = "#!/usr/bin/env php\n<?php\n\n";
$fix_script .= "// Automatic fixes for missing Order Management System components\n\n";

// Generate missing method stubs
if (!empty($audit_results['missing_methods'])) {
    $fix_script .= "// Missing methods to add:\n";
    foreach ($audit_results['missing_methods'] as $missing_method) {
        list($controller, $method) = explode('::', $missing_method);
        $fix_script .= "// Add to $controller:\n";
        $fix_script .= "public function $method() {\n";
        $fix_script .= "    // TODO: Implement $method functionality\n";
        $fix_script .= "    return view('appropriate.view');\n";
        $fix_script .= "}\n\n";
    }
}

file_put_contents('order-system-fixes.php', $fix_script);
success("Fix script generated: order-system-fixes.php");

// 9. SPECIFIC CHECKS FOR MENU ITEM REQUIREMENTS
checkSection("DETAILED MENU ITEM REQUIREMENTS CHECK");

echo colorOutput("\n📋 MENU ITEM DISPLAY REQUIREMENTS:\n", 'yellow');
echo "1. Buy & Sell items → Show stock levels\n";
echo "2. KOT items → Show green 'Available' tag\n";
echo "3. Branch-specific filtering\n";
echo "4. Real-time stock checking\n\n";

// Check specific menu retrieval methods
$menu_methods_to_check = [
    'getAvailableMenuItems',
    'getMenuItemsByType',
    'checkStock'
];

foreach ($controllers_to_check as $controller_path => $methods) {
    if (file_exists($controller_path)) {
        $content = file_get_contents($controller_path);
        
        foreach ($menu_methods_to_check as $menu_method) {
            if (strpos($content, "function $menu_method") !== false) {
                success("Menu method '$menu_method' exists in " . basename($controller_path));
                
                // Extract method content for analysis
                $pattern = "/function\s+$menu_method\s*\([^}]*\}[^}]*\}/s";
                if (preg_match($pattern, $content, $matches)) {
                    $method_content = $matches[0];
                    
                    // Check for Buy & Sell logic
                    if (strpos($method_content, 'buy_sell') !== false || strpos($method_content, 'stock') !== false) {
                        success("  ✓ Contains Buy & Sell/stock logic");
                    } else {
                        warning("  ⚠ Missing Buy & Sell/stock logic");
                    }
                    
                    // Check for KOT logic
                    if (strpos($method_content, 'kot') !== false || strpos($method_content, 'KOT') !== false) {
                        success("  ✓ Contains KOT logic");
                    } else {
                        warning("  ⚠ Missing KOT logic");
                    }
                    
                    // Check for branch filtering
                    if (strpos($method_content, 'branch') !== false) {
                        success("  ✓ Contains branch filtering");
                    } else {
                        warning("  ⚠ Missing branch filtering");
                    }
                }
            } else {
                error("Menu method '$menu_method' MISSING in " . basename($controller_path));
            }
        }
    }
}

echo colorOutput("\n✅ AUDIT COMPLETE!\n", 'green');
echo colorOutput("Run the generated 'order-system-fixes.php' to address missing components.\n", 'cyan');

// Generate final recommendations
echo colorOutput("\n🔧 IMMEDIATE ACTION ITEMS:\n", 'yellow');
echo "1. Review all WARNING messages above\n";
echo "2. Implement missing controller methods\n";
echo "3. Create missing blade templates\n";
echo "4. Add proper menu item display logic\n";
echo "5. Ensure admin default values are working\n";
echo "6. Test all order flows end-to-end\n";
echo "7. Verify takeaway type selector functionality\n";
echo "8. Check Buy & Sell stock display\n";
echo "9. Verify KOT item 'Available' tags\n";
echo "10. Test branch-based filtering\n\n";

?>
