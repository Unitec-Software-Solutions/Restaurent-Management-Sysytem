<?php
/**
 * Comprehensive Order Flow Verification Script
 * Checks all order management flows and menu item display functionality
 */

require 'vendor/autoload.php';

class OrderFlowVerifier
{
    private $issues = [];
    private $basePath;

    public function __construct()
    {
        $this->basePath = __DIR__;
    }

    public function verifyAll()
    {
        echo "🔍 COMPREHENSIVE ORDER FLOW VERIFICATION\n";
        echo "=====================================\n\n";

        $this->verifyControllerMethods();
        $this->verifyBladeTemplates();
        $this->verifyRoutes();
        $this->verifyMenuItemLogic();
        $this->verifyOrderFlows();
        $this->verifyAdminFeatures();

        $this->generateReport();
    }

    private function verifyControllerMethods()
    {
        echo "📋 1. VERIFYING CONTROLLER METHODS\n";
        echo "================================\n";

        // Check OrderController methods
        $orderControllerPath = $this->basePath . '/app/Http/Controllers/OrderController.php';
        $this->checkControllerFile($orderControllerPath, 'OrderController', [
            'create' => 'Show order creation form',
            'store' => 'Store new order',
            'summary' => 'Show order summary with submit/update/add-another options',
            'edit' => 'Show order edit form', 
            'update' => 'Update existing order',
            'createTakeaway' => 'Show takeaway order creation form',
            'storeTakeaway' => 'Store takeaway order',
            'indexTakeaway' => 'List takeaway orders',
            'editTakeaway' => 'Edit takeaway order',
            'updateTakeaway' => 'Update takeaway order',
            'showTakeaway' => 'Show takeaway order details',
            'getAvailableMenuItems' => 'Get menu items with stock/availability info'
        ]);

        // Check AdminOrderController methods
        $adminOrderControllerPath = $this->basePath . '/app/Http/Controllers/AdminOrderController.php';
        $this->checkControllerFile($adminOrderControllerPath, 'AdminOrderController', [
            'create' => 'Admin order creation with pre-filled defaults',
            'store' => 'Store admin order with branch filtering',
            'index' => 'List all orders for admin branch management',
            'summary' => 'Admin order summary with enhanced options',
            'edit' => 'Admin order editing with defaults',
            'update' => 'Update admin order',
            'createTakeaway' => 'Admin takeaway creation with type selector',
            'takeawayTypeSelector' => 'Show takeaway type selector (call/in-house)',
            'storeTakeaway' => 'Store admin takeaway with defaults',
            'indexTakeaway' => 'List takeaway orders for admin',
            'editTakeaway' => 'Edit admin takeaway order',
            'updateTakeaway' => 'Update admin takeaway order',
            'showTakeaway' => 'Show admin takeaway details with management options'
        ]);

        echo "\n";
    }

    private function verifyBladeTemplates()
    {
        echo "🎨 2. VERIFYING BLADE TEMPLATES\n";
        echo "==============================\n";

        $bladeTemplates = [
            // Customer Order Templates
            'resources/views/orders/create.blade.php' => 'Customer order creation form',
            'resources/views/orders/summary.blade.php' => 'Order summary with submit/update/add-another buttons',
            'resources/views/orders/edit.blade.php' => 'Order edit form',
            'resources/views/orders/show.blade.php' => 'Order details display',
            'resources/views/orders/reservation-order-summary.blade.php' => 'Reservation-linked order summary',
            
            // Customer Takeaway Templates
            'resources/views/orders/takeaway/create.blade.php' => 'Customer takeaway creation',
            'resources/views/orders/takeaway/summary.blade.php' => 'Takeaway order summary',
            'resources/views/orders/takeaway/edit.blade.php' => 'Takeaway order edit',
            'resources/views/orders/takeaway/show.blade.php' => 'Takeaway order details',
            'resources/views/orders/takeaway/index.blade.php' => 'Takeaway orders list',
            
            // Admin Order Templates
            'resources/views/admin/orders/create.blade.php' => 'Admin order creation with defaults',
            'resources/views/admin/orders/summary.blade.php' => 'Admin order summary with management options',
            'resources/views/admin/orders/edit.blade.php' => 'Admin order edit with defaults',
            'resources/views/admin/orders/index.blade.php' => 'Admin orders list with branch filtering',
            'resources/views/admin/orders/show.blade.php' => 'Admin order details with status management',
            
            // Admin Takeaway Templates
            'resources/views/admin/orders/takeaway/create.blade.php' => 'Admin takeaway creation',
            'resources/views/admin/orders/takeaway-type-selector.blade.php' => 'Takeaway type selector (call/in-house)',
            'resources/views/admin/orders/takeaway/summary.blade.php' => 'Admin takeaway summary',
            'resources/views/admin/orders/takeaway/edit.blade.php' => 'Admin takeaway edit',
            'resources/views/admin/orders/takeaway/show.blade.php' => 'Admin takeaway details',
            'resources/views/admin/orders/takeaway/index.blade.php' => 'Admin takeaway orders list'
        ];

        foreach ($bladeTemplates as $template => $description) {
            $this->checkBladeTemplate($template, $description);
        }

        echo "\n";
    }

    private function verifyRoutes()
    {
        echo "🛣️  3. VERIFYING ROUTES\n";
        echo "=====================\n";

        $routeFiles = [
            'routes/web.php',
            'routes/groups/public.php',
            'routes/groups/admin.php'
        ];

        $requiredRoutes = [
            // Customer Order Routes
            'orders.create' => 'Customer order creation',
            'orders.store' => 'Store customer order',
            'orders.summary' => 'Customer order summary',
            'orders.edit' => 'Edit customer order',
            'orders.update' => 'Update customer order',
            'orders.show' => 'Show customer order',
            
            // Customer Takeaway Routes
            'orders.takeaway.create' => 'Customer takeaway creation',
            'orders.takeaway.store' => 'Store customer takeaway',
            'orders.takeaway.summary' => 'Customer takeaway summary',
            'orders.takeaway.edit' => 'Edit customer takeaway',
            'orders.takeaway.update' => 'Update customer takeaway',
            'orders.takeaway.show' => 'Show customer takeaway',
            'orders.takeaway.index' => 'List customer takeaways',
            
            // Admin Order Routes
            'admin.orders.create' => 'Admin order creation',
            'admin.orders.store' => 'Store admin order',
            'admin.orders.summary' => 'Admin order summary',
            'admin.orders.edit' => 'Edit admin order',
            'admin.orders.update' => 'Update admin order',
            'admin.orders.show' => 'Show admin order',
            'admin.orders.index' => 'List admin orders',
            
            // Admin Takeaway Routes
            'admin.orders.takeaway.create' => 'Admin takeaway creation',
            'admin.orders.takeaway.type-selector' => 'Takeaway type selector',
            'admin.orders.takeaway.store' => 'Store admin takeaway',
            'admin.orders.takeaway.summary' => 'Admin takeaway summary',
            'admin.orders.takeaway.edit' => 'Edit admin takeaway',
            'admin.orders.takeaway.update' => 'Update admin takeaway',
            'admin.orders.takeaway.show' => 'Show admin takeaway',
            'admin.orders.takeaway.index' => 'List admin takeaways'
        ];

        foreach ($routeFiles as $routeFile) {
            $this->checkRouteFile($routeFile, $requiredRoutes);
        }

        echo "\n";
    }

    private function verifyMenuItemLogic()
    {
        echo "🍽️  4. VERIFYING MENU ITEM DISPLAY LOGIC\n";
        echo "=======================================\n";

        // Check menu item retrieval logic in controllers
        $this->checkMenuItemLogicInFile('app/Http/Controllers/OrderController.php');
        $this->checkMenuItemLogicInFile('app/Http/Controllers/AdminOrderController.php');

        // Check menu item display in blade templates
        $orderBlades = [
            'resources/views/orders/create.blade.php',
            'resources/views/orders/takeaway/create.blade.php',
            'resources/views/admin/orders/create.blade.php',
            'resources/views/admin/orders/takeaway/create.blade.php'
        ];

        foreach ($orderBlades as $blade) {
            $this->checkMenuDisplayInBlade($blade);
        }

        echo "\n";
    }

    private function verifyOrderFlows()
    {
        echo "🔄 5. VERIFYING ORDER FLOWS\n";
        echo "==========================\n";

        $flows = [
            'Reservation Order Flow' => [
                'create' => 'Show create form with reservation context',
                'summary' => 'Show summary with submit/update/add-another buttons',
                'submit' => 'Redirect to reservation details with order',
                'update' => 'Edit order and return to summary',
                'add-another' => 'Return to create form'
            ],
            'Takeaway Order Flow' => [
                'create' => 'Show takeaway create form',
                'summary' => 'Show takeaway summary with options',
                'submit' => 'Show order details by number',
                'update' => 'Edit takeaway and return to summary',
                'add-another' => 'Return to takeaway create form'
            ],
            'Admin Order Flow' => [
                'create' => 'Show form with pre-filled defaults',
                'branch-filtering' => 'Filter by admin branch',
                'type-selector' => 'Choose call/in-house for takeaway',
                'management' => 'View/update/cancel/status change options'
            ]
        ];

        foreach ($flows as $flowName => $steps) {
            echo "  📋 {$flowName}:\n";
            foreach ($steps as $step => $description) {
                $this->verifyFlowStep($flowName, $step, $description);
            }
            echo "\n";
        }
    }

    private function verifyAdminFeatures()
    {
        echo "👨‍💼 6. VERIFYING ADMIN-SPECIFIC FEATURES\n";
        echo "=======================================\n";

        $adminFeatures = [
            'Pre-filled Defaults' => 'Admin forms have default values',
            'Branch Filtering' => 'Orders filtered by admin branch',
            'Order Management' => 'View/update/cancel/status change options',
            'Takeaway Type Selector' => 'Call vs In-house selection',
            'Status Management' => 'Change order statuses',
            'Branch-wide View' => 'See all branch orders'
        ];

        foreach ($adminFeatures as $feature => $description) {
            $this->verifyAdminFeature($feature, $description);
        }

        echo "\n";
    }

    private function checkControllerFile($filePath, $controllerName, $requiredMethods)
    {
        if (!file_exists($filePath)) {
            $this->issues[] = "❌ {$controllerName} file missing: {$filePath}";
            echo "  ❌ {$controllerName} file missing\n";
            return;
        }

        $content = file_get_contents($filePath);
        $missingMethods = [];

        foreach ($requiredMethods as $method => $description) {
            if (!preg_match("/function\s+{$method}\s*\(/", $content)) {
                $missingMethods[] = "{$method}() - {$description}";
            }
        }

        if (empty($missingMethods)) {
            echo "  ✅ {$controllerName} - All methods present\n";
        } else {
            echo "  ❌ {$controllerName} - Missing methods:\n";
            foreach ($missingMethods as $method) {
                echo "    • {$method}\n";
                $this->issues[] = "Missing method in {$controllerName}: {$method}";
            }
        }
    }

    private function checkBladeTemplate($templatePath, $description)
    {
        $fullPath = $this->basePath . '/' . $templatePath;
        
        if (!file_exists($fullPath)) {
            echo "  ❌ Missing: {$templatePath} - {$description}\n";
            $this->issues[] = "Missing blade template: {$templatePath}";
            return;
        }

        $content = file_get_contents($fullPath);
        
        // Check if template has meaningful content (not just placeholder)
        $contentChecks = [
            'extends' => '@extends',
            'section' => '@section',
            'content_length' => strlen(trim($content)) > 200
        ];

        $hasContent = true;
        foreach ($contentChecks as $check => $pattern) {
            if ($check === 'content_length') {
                if (!$pattern) $hasContent = false;
            } else {
                if (strpos($content, $pattern) === false) $hasContent = false;
            }
        }

        if ($hasContent) {
            echo "  ✅ Present: {$templatePath}\n";
        } else {
            echo "  ⚠️  Placeholder: {$templatePath} - Needs implementation\n";
            $this->issues[] = "Blade template needs implementation: {$templatePath}";
        }
    }

    private function checkRouteFile($routeFile, $requiredRoutes)
    {
        $fullPath = $this->basePath . '/' . $routeFile;
        
        if (!file_exists($fullPath)) {
            echo "  ❌ Route file missing: {$routeFile}\n";
            $this->issues[] = "Route file missing: {$routeFile}";
            return;
        }

        $content = file_get_contents($fullPath);
        $foundRoutes = [];
        $missingRoutes = [];

        foreach ($requiredRoutes as $routeName => $description) {
            if (preg_match("/name\(\s*['\"]" . preg_quote($routeName, '/') . "['\"]\s*\)/", $content)) {
                $foundRoutes[] = $routeName;
            } else {
                $missingRoutes[] = "{$routeName} - {$description}";
            }
        }

        echo "  📄 {$routeFile}:\n";
        echo "    ✅ Found routes: " . count($foundRoutes) . "\n";
        
        if (!empty($missingRoutes)) {
            echo "    ❌ Missing routes: " . count($missingRoutes) . "\n";
            foreach ($missingRoutes as $route) {
                $this->issues[] = "Missing route in {$routeFile}: {$route}";
            }
        }
    }

    private function checkMenuItemLogicInFile($filePath)
    {
        $fullPath = $this->basePath . '/' . $filePath;
        
        if (!file_exists($fullPath)) {
            echo "  ❌ Controller missing: {$filePath}\n";
            return;
        }

        $content = file_get_contents($fullPath);
        
        // Check for menu item retrieval logic
        $requiredLogic = [
            'stock_check' => ['stock', 'quantity', 'available_quantity'],
            'menu_filtering' => ['active', 'status', 'where'],
            'item_types' => ['buy_sell', 'kot', 'item_type'],
            'branch_filtering' => ['branch_id', 'organization_id'],
            'price_validation' => ['buy_price', 'sell_price', 'price']
        ];

        $foundLogic = [];
        $missingLogic = [];

        foreach ($requiredLogic as $logicType => $keywords) {
            $found = false;
            foreach ($keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $foundLogic[] = $logicType;
            } else {
                $missingLogic[] = $logicType;
            }
        }

        echo "  📋 " . basename($filePath) . ":\n";
        echo "    ✅ Menu logic found: " . implode(', ', $foundLogic) . "\n";
        
        if (!empty($missingLogic)) {
            echo "    ❌ Missing logic: " . implode(', ', $missingLogic) . "\n";
            foreach ($missingLogic as $logic) {
                $this->issues[] = "Missing menu logic in " . basename($filePath) . ": {$logic}";
            }
        }
    }

    private function checkMenuDisplayInBlade($bladePath)
    {
        $fullPath = $this->basePath . '/' . $bladePath;
        
        if (!file_exists($fullPath)) {
            return;
        }

        $content = file_get_contents($fullPath);
        
        // Check for menu item display features
        $displayFeatures = [
            'stock_display' => ['stock', 'quantity', 'available'],
            'availability_badge' => ['available', 'badge', 'green'],
            'item_type_handling' => ['buy_sell', 'kot', 'item_type'],
            'price_display' => ['price', 'sell_price', 'cost']
        ];

        $foundFeatures = [];
        $missingFeatures = [];

        foreach ($displayFeatures as $feature => $keywords) {
            $found = false;
            foreach ($keywords as $keyword) {
                if (stripos($content, $keyword) !== false) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $foundFeatures[] = $feature;
            } else {
                $missingFeatures[] = $feature;
            }
        }

        echo "  🎨 " . basename($bladePath) . ":\n";
        echo "    ✅ Display features: " . implode(', ', $foundFeatures) . "\n";
        
        if (!empty($missingFeatures)) {
            echo "    ❌ Missing features: " . implode(', ', $missingFeatures) . "\n";
            foreach ($missingFeatures as $feature) {
                $this->issues[] = "Missing display feature in " . basename($bladePath) . ": {$feature}";
            }
        }
    }

    private function verifyFlowStep($flowName, $step, $description)
    {
        // This is a conceptual check - in a real implementation,
        // you would check specific files and logic for each flow step
        echo "    • {$step}: {$description}\n";
        
        // Add specific checks based on the flow step
        switch ($step) {
            case 'summary':
                $this->checkSummaryPageButtons();
                break;
            case 'type-selector':
                $this->checkTakeawayTypeSelector();
                break;
            case 'branch-filtering':
                $this->checkBranchFiltering();
                break;
        }
    }

    private function checkSummaryPageButtons()
    {
        $summaryPages = [
            'resources/views/orders/summary.blade.php',
            'resources/views/orders/takeaway/summary.blade.php',
            'resources/views/orders/reservation-order-summary.blade.php'
        ];

        foreach ($summaryPages as $page) {
            $fullPath = $this->basePath . '/' . $page;
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                $buttons = ['submit', 'update', 'add-another', 'edit'];
                $foundButtons = [];
                
                foreach ($buttons as $button) {
                    if (stripos($content, $button) !== false) {
                        $foundButtons[] = $button;
                    }
                }
                
                if (count($foundButtons) < 3) {
                    $this->issues[] = "Summary page missing buttons: " . basename($page);
                }
            }
        }
    }

    private function checkTakeawayTypeSelector()
    {
        $selectorPath = $this->basePath . '/resources/views/admin/orders/takeaway-type-selector.blade.php';
        if (!file_exists($selectorPath)) {
            $this->issues[] = "Missing takeaway type selector template";
            return;
        }

        $content = file_get_contents($selectorPath);
        if (stripos($content, 'call') === false || stripos($content, 'in-house') === false) {
            $this->issues[] = "Takeaway type selector missing call/in-house options";
        }
    }

    private function checkBranchFiltering()
    {
        $adminControllerPath = $this->basePath . '/app/Http/Controllers/AdminOrderController.php';
        if (file_exists($adminControllerPath)) {
            $content = file_get_contents($adminControllerPath);
            if (stripos($content, 'branch') === false && stripos($content, 'organization') === false) {
                $this->issues[] = "Admin controller missing branch filtering logic";
            }
        }
    }

    private function verifyAdminFeature($feature, $description)
    {
        echo "  📋 {$feature}: {$description}\n";
        
        // Add specific checks for admin features
        switch ($feature) {
            case 'Pre-filled Defaults':
                $this->checkPrefilledDefaults();
                break;
            case 'Branch Filtering':
                $this->checkBranchFiltering();
                break;
            case 'Takeaway Type Selector':
                $this->checkTakeawayTypeSelector();
                break;
        }
    }

    private function checkPrefilledDefaults()
    {
        $adminBlades = [
            'resources/views/admin/orders/create.blade.php',
            'resources/views/admin/orders/takeaway/create.blade.php'
        ];

        foreach ($adminBlades as $blade) {
            $fullPath = $this->basePath . '/' . $blade;
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                if (stripos($content, 'value=') === false && stripos($content, 'selected') === false) {
                    $this->issues[] = "Admin form missing pre-filled defaults: " . basename($blade);
                }
            }
        }
    }

    private function generateReport()
    {
        echo "📊 VERIFICATION REPORT\n";
        echo "=====================\n\n";

        if (empty($this->issues)) {
            echo "🎉 ALL CHECKS PASSED!\n";
            echo "Your order management system appears to be complete.\n";
        } else {
            echo "⚠️  ISSUES FOUND: " . count($this->issues) . "\n\n";
            
            $groupedIssues = [];
            foreach ($this->issues as $issue) {
                $category = $this->categorizeIssue($issue);
                $groupedIssues[$category][] = $issue;
            }

            foreach ($groupedIssues as $category => $issues) {
                echo "📂 {$category}:\n";
                foreach ($issues as $issue) {
                    echo "  • {$issue}\n";
                }
                echo "\n";
            }

            echo "🔧 RECOMMENDED ACTIONS:\n";
            echo "======================\n";
            $this->generateRecommendations($groupedIssues);
        }

        echo "\n✅ Verification completed at " . date('Y-m-d H:i:s') . "\n";
    }

    private function categorizeIssue($issue)
    {
        if (strpos($issue, 'controller') !== false || strpos($issue, 'method') !== false) {
            return 'Controller Issues';
        } elseif (strpos($issue, 'blade') !== false || strpos($issue, 'template') !== false) {
            return 'Template Issues';
        } elseif (strpos($issue, 'route') !== false) {
            return 'Route Issues';
        } elseif (strpos($issue, 'menu') !== false) {
            return 'Menu Logic Issues';
        } else {
            return 'General Issues';
        }
    }

    private function generateRecommendations($groupedIssues)
    {
        foreach ($groupedIssues as $category => $issues) {
            switch ($category) {
                case 'Controller Issues':
                    echo "  🎯 Add missing controller methods with proper logic\n";
                    echo "  🎯 Implement menu item retrieval with stock checking\n";
                    echo "  🎯 Add admin-specific features and defaults\n";
                    break;
                case 'Template Issues':
                    echo "  🎯 Create missing blade templates\n";
                    echo "  🎯 Implement proper menu item display with stock/availability\n";
                    echo "  🎯 Add summary page buttons (submit/update/add-another)\n";
                    break;
                case 'Route Issues':
                    echo "  🎯 Add missing routes in appropriate route files\n";
                    echo "  🎯 Ensure proper middleware and naming conventions\n";
                    break;
                case 'Menu Logic Issues':
                    echo "  🎯 Implement proper menu item filtering by status/branch\n";
                    echo "  🎯 Add stock level checking for Buy & Sell items\n";
                    echo "  🎯 Add availability badges for KOT items\n";
                    break;
            }
        }
    }
}

// Run the verification
$verifier = new OrderFlowVerifier();
$verifier->verifyAll();
