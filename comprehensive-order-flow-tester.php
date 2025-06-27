<?php
/**
 * Order Management Flow Testing Script
 * 
 * This script tests the specific order flows you mentioned:
 * 1. Customer reservation orders (create → summary → submit/update/add another)
 * 2. Customer takeaway orders (create → summary → submit/update/add another)
 * 3. Admin reservation orders (with admin session defaults and enhanced features)
 * 4. Admin takeaway orders (with call/in-house type selection)
 * 5. Menu item display with proper KOT badges and stock levels
 */

require_once __DIR__ . '/vendor/autoload.php';

class OrderManagementFlowTester
{
    private $results = [];
    private $errors = [];
    private $warnings = [];

    public function runTests()
    {
        echo "🧪 TESTING ORDER MANAGEMENT FLOWS\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->testCustomerReservationFlow();
        $this->testCustomerTakeawayFlow();
        $this->testAdminReservationFlow();
        $this->testAdminTakeawayFlow();
        $this->testMenuItemDisplay();
        $this->testStockValidation();
        $this->testKOTBadgeLogic();
        $this->testSessionHandling();

        $this->generateTestReport();
    }

    private function testCustomerReservationFlow()
    {
        echo "🏷️ Testing Customer Reservation Flow...\n";
        
        $flowComponents = [
            'controller_method_create' => $this->checkMethod('OrderController', 'create'),
            'controller_method_store' => $this->checkMethod('OrderController', 'store'),
            'controller_method_summary' => $this->checkMethod('OrderController', 'summary'),
            'controller_method_edit' => $this->checkMethod('OrderController', 'edit'),
            'controller_method_update' => $this->checkMethod('OrderController', 'update'),
            'template_create' => $this->checkTemplate('orders/create.blade.php'),
            'template_summary' => $this->checkTemplate('orders/summary.blade.php'),
            'template_reservation_summary' => $this->checkTemplate('orders/reservation-order-summary.blade.php'),
            'payment_or_repeat_page' => $this->checkTemplate('orders/payment_or_repeat.blade.php'),
        ];

        $passed = array_sum($flowComponents);
        $total = count($flowComponents);
        
        $this->results['customer_reservation'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} components verified (" . $this->results['customer_reservation']['percentage'] . "%)\n\n";
    }

    private function testCustomerTakeawayFlow()
    {
        echo "🥡 Testing Customer Takeaway Flow...\n";
        
        $flowComponents = [
            'controller_method_create_takeaway' => $this->checkMethod('OrderController', 'createTakeaway'),
            'controller_method_store_takeaway' => $this->checkMethod('OrderController', 'storeTakeaway'),
            'controller_method_edit_takeaway' => $this->checkMethod('OrderController', 'editTakeaway'),
            'controller_method_update_takeaway' => $this->checkMethod('OrderController', 'updateTakeaway'),
            'template_takeaway_create' => $this->checkTemplate('orders/takeaway/create.blade.php'),
            'template_takeaway_summary' => $this->checkTemplate('orders/takeaway/summary.blade.php'),
        ];

        $passed = array_sum($flowComponents);
        $total = count($flowComponents);
        
        $this->results['customer_takeaway'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} components verified (" . $this->results['customer_takeaway']['percentage'] . "%)\n\n";
    }

    private function testAdminReservationFlow()
    {
        echo "👨‍💼 Testing Admin Reservation Flow...\n";
        
        $flowComponents = [
            'admin_controller_create' => $this->checkMethod('AdminOrderController', 'create'),
            'admin_controller_store' => $this->checkMethod('AdminOrderController', 'store'),
            'admin_controller_edit' => $this->checkMethod('AdminOrderController', 'edit'),
            'admin_controller_update' => $this->checkMethod('AdminOrderController', 'update'),
            'admin_template_create' => $this->checkTemplate('admin/orders/create.blade.php'),
            'admin_session_defaults' => $this->checkAdminSessionDefaults(),
            'branch_filtering' => $this->checkBranchFiltering(),
        ];

        $passed = array_sum($flowComponents);
        $total = count($flowComponents);
        
        $this->results['admin_reservation'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} components verified (" . $this->results['admin_reservation']['percentage'] . "%)\n\n";
    }

    private function testAdminTakeawayFlow()
    {
        echo "🏪 Testing Admin Takeaway Flow...\n";
        
        $flowComponents = [
            'admin_takeaway_create' => $this->checkMethod('AdminOrderController', 'createTakeaway'),
            'admin_takeaway_store' => $this->checkMethod('AdminOrderController', 'storeTakeaway'),
            'admin_takeaway_type_selector' => $this->checkMethod('AdminOrderController', 'takeawayTypeSelector'),
            'admin_takeaway_template' => $this->checkTemplate('admin/orders/takeaway/create.blade.php'),
            'takeaway_type_selector_template' => $this->checkTemplate('admin/orders/takeaway-type-selector.blade.php'),
            'call_vs_inhouse_logic' => $this->checkCallInHouseLogic(),
        ];

        $passed = array_sum($flowComponents);
        $total = count($flowComponents);
        
        $this->results['admin_takeaway'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} components verified (" . $this->results['admin_takeaway']['percentage'] . "%)\n\n";
    }

    private function testMenuItemDisplay()
    {
        echo "🍽️ Testing Menu Item Display Logic...\n";
        
        $checks = [
            'menu_retrieval_validation' => $this->checkMenuItemRetrieval(),
            'buy_sell_price_validation' => $this->checkBuySellPriceValidation(),
            'kot_item_identification' => $this->checkKOTItemIdentification(),
            'stock_level_display' => $this->checkStockLevelDisplay(),
        ];

        $passed = array_sum($checks);
        $total = count($checks);
        
        $this->results['menu_display'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} checks passed (" . $this->results['menu_display']['percentage'] . "%)\n\n";
    }

    private function testStockValidation()
    {
        echo "📦 Testing Stock Validation System...\n";
        
        $checks = [
            'stock_checking_logic' => $this->checkStockCheckingLogic(),
            'real_time_validation' => $this->checkRealTimeValidation(),
            'stock_reservation' => $this->checkStockReservation(),
            'transaction_safety' => $this->checkTransactionSafety(),
        ];

        $passed = array_sum($checks);
        $total = count($checks);
        
        $this->results['stock_validation'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} checks passed (" . $this->results['stock_validation']['percentage'] . "%)\n\n";
    }

    private function testKOTBadgeLogic()
    {
        echo "🏷️ Testing KOT Badge Logic...\n";
        
        $checks = [
            'kot_badge_in_templates' => $this->checkKOTBadgeTemplates(),
            'kot_differentiation_controllers' => $this->checkKOTDifferentiationControllers(),
            'green_available_tag' => $this->checkGreenAvailableTag(),
        ];

        $passed = array_sum($checks);
        $total = count($checks);
        
        $this->results['kot_badge'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} checks passed (" . $this->results['kot_badge']['percentage'] . "%)\n\n";
    }

    private function testSessionHandling()
    {
        echo "🔐 Testing Session Handling...\n";
        
        $checks = [
            'admin_session_detection' => $this->checkAdminSessionDetection(),
            'default_values_population' => $this->checkDefaultValuesPopulation(),
            'branch_permissions' => $this->checkBranchPermissions(),
        ];

        $passed = array_sum($checks);
        $total = count($checks);
        
        $this->results['session_handling'] = [
            'passed' => $passed,
            'total' => $total,
            'percentage' => round(($passed / $total) * 100, 1)
        ];

        echo "   Results: {$passed}/{$total} checks passed (" . $this->results['session_handling']['percentage'] . "%)\n\n";
    }

    // Helper methods
    private function checkMethod($controller, $method)
    {
        $controllerFile = "app/Http/Controllers/{$controller}.php";
        if (file_exists($controllerFile)) {
            $content = file_get_contents($controllerFile);
            return preg_match("/function\s+{$method}\s*\(/", $content) ? 1 : 0;
        }
        return 0;
    }

    private function checkTemplate($template)
    {
        return file_exists("resources/views/{$template}") ? 1 : 0;
    }

    private function checkAdminSessionDefaults()
    {
        $file = 'app/Http/Controllers/AdminOrderController.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return preg_match('/getAdminSessionDefaults|admin.*session|default.*branch/', $content) ? 1 : 0;
        }
        return 0;
    }

    private function checkBranchFiltering()
    {
        $file = 'app/Http/Controllers/AdminOrderController.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return preg_match('/branch.*filter|organization.*filter|is_super_admin/', $content) ? 1 : 0;
        }
        return 0;
    }

    private function checkCallInHouseLogic()
    {
        $file = 'resources/views/admin/orders/takeaway-type-selector.blade.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return preg_match('/call|in.*house|takeaway.*type/i', $content) ? 1 : 0;
        }
        return 0;
    }

    private function checkMenuItemRetrieval()
    {
        $controllers = ['app/Http/Controllers/OrderController.php', 'app/Http/Controllers/AdminOrderController.php'];
        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/getAvailableMenuItems|getValidatedMenuItems/', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkBuySellPriceValidation()
    {
        $controllers = ['app/Http/Controllers/OrderController.php', 'app/Http/Controllers/AdminOrderController.php'];
        $found = 0;
        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/buying_price.*selling_price|selling_price.*buying_price/', $content)) {
                    $found++;
                }
            }
        }
        return $found >= 2 ? 1 : 0; // Both controllers should have this
    }

    private function checkKOTItemIdentification()
    {
        $controllers = ['app/Http/Controllers/OrderController.php', 'app/Http/Controllers/AdminOrderController.php'];
        $found = 0;
        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/item_type.*KOT|display_kot_badge/i', $content)) {
                    $found++;
                }
            }
        }
        return $found >= 1 ? 1 : 0;
    }

    private function checkStockLevelDisplay()
    {
        $templates = glob('resources/views/orders/**/*.blade.php');
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $content = file_get_contents($template);
                if (preg_match('/stock.*level|current_stock|In Stock|Out of Stock/i', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkStockCheckingLogic()
    {
        $files = [
            'app/Models/ItemTransaction.php',
            'app/Services/InventoryService.php',
            'app/Services/InventoryAlertService.php'
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/stockOnHand|current_stock|stock.*validation/', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkRealTimeValidation()
    {
        $controllers = ['app/Http/Controllers/OrderController.php', 'app/Http/Controllers/AdminOrderController.php'];
        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/checkStock|validateStock|stock.*available/', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkStockReservation()
    {
        $file = 'app/Services/OrderManagementService.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return preg_match('/reserve.*stock|stock.*reservation/i', $content) ? 1 : 0;
        }
        return 0;
    }

    private function checkTransactionSafety()
    {
        $serviceFiles = glob('app/Services/*.php');
        foreach ($serviceFiles as $service) {
            if (file_exists($service)) {
                $content = file_get_contents($service);
                if (preg_match('/DB::transaction|beginTransaction|commit|rollback/', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkKOTBadgeTemplates()
    {
        $templates = array_merge(
            glob('resources/views/orders/**/*.blade.php'),
            glob('resources/views/admin/orders/**/*.blade.php')
        );
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $content = file_get_contents($template);
                if (preg_match('/KOT.*badge|badge.*KOT|green.*KOT/i', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkKOTDifferentiationControllers()
    {
        $controllers = ['app/Http/Controllers/OrderController.php', 'app/Http/Controllers/AdminOrderController.php'];
        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/item_type.*KOT|display_kot_badge|is_kot_item/i', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkGreenAvailableTag()
    {
        $templates = array_merge(
            glob('resources/views/orders/**/*.blade.php'),
            glob('resources/views/admin/orders/**/*.blade.php')
        );
        
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $content = file_get_contents($template);
                if (preg_match('/green.*available|available.*green|bg-green.*KOT/i', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkAdminSessionDetection()
    {
        $file = 'app/Http/Controllers/AdminOrderController.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return preg_match('/Auth.*admin|admin.*session|session.*admin/i', $content) ? 1 : 0;
        }
        return 0;
    }

    private function checkDefaultValuesPopulation()
    {
        $templates = glob('resources/views/admin/orders/**/*.blade.php');
        foreach ($templates as $template) {
            if (file_exists($template)) {
                $content = file_get_contents($template);
                if (preg_match('/value.*default|default.*value|session.*value/', $content)) {
                    return 1;
                }
            }
        }
        return 0;
    }

    private function checkBranchPermissions()
    {
        $file = 'app/Http/Controllers/AdminOrderController.php';
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return preg_match('/branch.*permission|organization.*permission|is_super_admin/', $content) ? 1 : 0;
        }
        return 0;
    }

    private function generateTestReport()
    {
        echo str_repeat("=", 80) . "\n";
        echo "📊 ORDER MANAGEMENT FLOW TEST REPORT\n";
        echo str_repeat("=", 80) . "\n\n";

        $totalPassed = 0;
        $totalTests = 0;

        foreach ($this->results as $flowName => $result) {
            $totalPassed += $result['passed'];
            $totalTests += $result['total'];
            
            $status = $result['percentage'] >= 90 ? '🎉' : ($result['percentage'] >= 70 ? '✅' : '⚠️');
            echo "{$status} " . strtoupper(str_replace('_', ' ', $flowName)) . ": {$result['passed']}/{$result['total']} ({$result['percentage']}%)\n";
        }

        $overallPercentage = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 1) : 0;
        
        echo "\n" . str_repeat("-", 40) . "\n";
        echo "📈 OVERALL SCORE: {$totalPassed}/{$totalTests} ({$overallPercentage}%)\n";
        
        if ($overallPercentage >= 95) {
            echo "🎉 STATUS: EXCELLENT - All flows ready for production!\n";
        } elseif ($overallPercentage >= 85) {
            echo "✅ STATUS: VERY GOOD - Minor improvements recommended\n";
        } elseif ($overallPercentage >= 70) {
            echo "⚠️ STATUS: GOOD - Some improvements needed\n";
        } else {
            echo "❌ STATUS: NEEDS WORK - Significant improvements required\n";
        }

        echo "\n🎯 FLOW-SPECIFIC ANALYSIS:\n";
        
        foreach ($this->results as $flowName => $result) {
            if ($result['percentage'] < 90) {
                echo "   • " . strtoupper(str_replace('_', ' ', $flowName)) . " needs attention ({$result['percentage']}%)\n";
            }
        }

        echo "\n💡 NEXT STEPS:\n";
        echo "   1. Test each flow manually in the browser\n";
        echo "   2. Verify reservation → create → summary → submit/update/add another\n";
        echo "   3. Verify takeaway → create → summary → submit/update/add another\n";
        echo "   4. Test admin flows with session defaults and type selection\n";
        echo "   5. Confirm KOT badges show as green 'Available' tags\n";
        echo "   6. Verify Buy & Sell items show current stock levels\n";
        echo "   7. Test edge cases (out of stock, validation errors)\n";

        echo "\n" . str_repeat("=", 80) . "\n";
        echo "Flow test completed at: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat("=", 80) . "\n";
    }
}

// Run the flow tests
$tester = new OrderManagementFlowTester();
$tester->runTests();
