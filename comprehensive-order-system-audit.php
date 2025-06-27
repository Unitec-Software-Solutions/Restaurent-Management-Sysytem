#!/usr/bin/env php
<?php
/**
 * Order Management System Comprehensive Audit
 * Purpose: Identify missing blades, controller methods, and flow logic
 * Date: 2025-01-27
 * 
 * This audit will check:
 * 1. Controller methods for order/reservation flows
 * 2. Blade templates for all required views
 * 3. Route completeness and redirect logic
 * 4. Session-based defaults and admin features
 * 5. Stock validation and transaction safety
 */

require_once 'vendor/autoload.php';

class OrderManagementSystemAudit
{
    private $results = [];
    private $errors = [];
    private $warnings = [];
    
    public function __construct()
    {
        echo "🔍 ORDER MANAGEMENT SYSTEM COMPREHENSIVE AUDIT\n";
        echo "=" . str_repeat("=", 55) . "\n\n";
    }

    public function runAudit(): void
    {
        $this->auditControllerMethods();
        $this->auditBladeTemplates();
        $this->auditRoutes();
        $this->auditSessionDefaults();
        $this->auditStockValidation();
        $this->auditTransactionSafety();
        $this->generateReport();
    }

    private function auditControllerMethods(): void
    {
        echo "1. CONTROLLER METHODS AUDIT\n";
        echo "============================\n";

        $controllers = [
            'OrderController' => 'app/Http/Controllers/OrderController.php',
            'AdminOrderController' => 'app/Http/Controllers/AdminOrderController.php',
        ];

        foreach ($controllers as $name => $path) {
            if (!file_exists($path)) {
                $this->errors[] = "Controller not found: {$name}";
                continue;
            }

            $content = file_get_contents($path);
            $this->auditControllerFile($name, $content);
        }
    }

    private function auditControllerFile(string $controllerName, string $content): void
    {
        $requiredMethods = $this->getRequiredControllerMethods($controllerName);
        
        echo "  Checking {$controllerName}...\n";
        
        foreach ($requiredMethods as $method => $description) {
            if (preg_match("/public\s+function\s+{$method}\s*\(/", $content)) {
                echo "    ✅ {$method} - {$description}\n";
                $this->results["{$controllerName}::{$method}"] = 'EXISTS';
            } else {
                echo "    ❌ {$method} - {$description} (MISSING)\n";
                $this->errors[] = "Missing method: {$controllerName}::{$method}";
                $this->results["{$controllerName}::{$method}"] = 'MISSING';
            }
        }
        echo "\n";
    }

    private function getRequiredControllerMethods(string $controllerName): array
    {
        $methods = [];
        
        if ($controllerName === 'OrderController') {
            $methods = [
                'index' => 'List orders (public)',
                'create' => 'Create order form (reservation/takeaway)',
                'store' => 'Store new order',
                'show' => 'Show order details',
                'edit' => 'Edit order form',
                'update' => 'Update existing order',
                'summary' => 'Order summary with branching options',
                'createTakeaway' => 'Create takeaway order form',
                'storeTakeaway' => 'Store takeaway order',
                'indexTakeaway' => 'List takeaway orders',
                'showTakeaway' => 'Show takeaway order',
                'editTakeaway' => 'Edit takeaway order',
                'updateTakeaway' => 'Update takeaway order',
                'submitTakeaway' => 'Submit takeaway order',
            ];
        } elseif ($controllerName === 'AdminOrderController') {
            $methods = [
                'index' => 'Admin: List all orders with branch filtering',
                'create' => 'Admin: Create order form with pre-fill',
                'store' => 'Admin: Store new order',
                'show' => 'Admin: Show order details',
                'edit' => 'Admin: Edit order form',
                'update' => 'Admin: Update order',
                'destroy' => 'Admin: Cancel/delete order',
                'createTakeaway' => 'Admin: Create takeaway with type selector',
                'storeTakeaway' => 'Admin: Store takeaway order',
                'indexTakeaway' => 'Admin: List takeaway orders by branch',
                'showTakeaway' => 'Admin: Show takeaway details',
                'editTakeaway' => 'Admin: Edit takeaway order',
                'updateTakeaway' => 'Admin: Update takeaway order',
                'destroyTakeaway' => 'Admin: Cancel takeaway order',
                'dashboard' => 'Admin: Order management dashboard',
            ];
        }
        
        return $methods;
    }

    private function auditBladeTemplates(): void
    {
        echo "2. BLADE TEMPLATES AUDIT\n";
        echo "=========================\n";

        $requiredBlades = [
            // User Order Flows
            'orders/create.blade.php' => 'User: Create dine-in order (reservation-linked)',
            'orders/summary.blade.php' => 'User: Order summary with branching options',
            'orders/edit.blade.php' => 'User: Edit existing order',
            'orders/show.blade.php' => 'User: Show order details',
            'orders/index.blade.php' => 'User: List orders',
            
            // User Takeaway Flows
            'orders/takeaway/create.blade.php' => 'User: Create takeaway order',
            'orders/takeaway/summary.blade.php' => 'User: Takeaway summary with options',
            'orders/takeaway/edit.blade.php' => 'User: Edit takeaway order',
            'orders/takeaway/show.blade.php' => 'User: Show takeaway details',
            'orders/takeaway/index.blade.php' => 'User: List takeaway orders',
            
            // Admin Order Flows
            'admin/orders/create.blade.php' => 'Admin: Create order with pre-fill defaults',
            'admin/orders/summary.blade.php' => 'Admin: Order summary',
            'admin/orders/edit.blade.php' => 'Admin: Edit order',
            'admin/orders/show.blade.php' => 'Admin: Show order details',
            'admin/orders/index.blade.php' => 'Admin: List orders with branch filtering',
            'admin/orders/dashboard.blade.php' => 'Admin: Order management dashboard',
            
            // Admin Takeaway Flows
            'admin/orders/takeaway/create.blade.php' => 'Admin: Create takeaway with type selector',
            'admin/orders/takeaway/summary.blade.php' => 'Admin: Takeaway summary',
            'admin/orders/takeaway/edit.blade.php' => 'Admin: Edit takeaway',
            'admin/orders/takeaway/show.blade.php' => 'Admin: Show takeaway details',
            'admin/orders/takeaway/index.blade.php' => 'Admin: List takeaway by branch',
            
            // Admin Reservation Orders
            'admin/orders/reservations/index.blade.php' => 'Admin: List reservation orders',
            'admin/orders/reservations/summary.blade.php' => 'Admin: Reservation order summary',
            
            // Missing Identified Blades
            'orders/reservation-order-summary.blade.php' => 'User: Reservation order summary (MISSING)',
            'admin/orders/takeaway-type-selector.blade.php' => 'Admin: Takeaway type selector (MISSING)',
        ];

        foreach ($requiredBlades as $bladePath => $description) {
            $fullPath = "resources/views/{$bladePath}";
            if (file_exists($fullPath)) {
                echo "  ✅ {$bladePath} - {$description}\n";
                $this->results["blade:{$bladePath}"] = 'EXISTS';
            } else {
                echo "  ❌ {$bladePath} - {$description} (MISSING)\n";
                $this->errors[] = "Missing blade: {$bladePath}";
                $this->results["blade:{$bladePath}"] = 'MISSING';
            }
        }
        echo "\n";
    }

    private function auditRoutes(): void
    {
        echo "3. ROUTE COMPLETENESS AUDIT\n";
        echo "============================\n";

        $routeFiles = [
            'routes/web.php',
            'routes/groups/public.php',
            'routes/groups/admin.php',
        ];

        $requiredRoutes = [
            // User Routes
            'orders.create' => 'User: Create order',
            'orders.store' => 'User: Store order',
            'orders.summary' => 'User: Order summary',
            'orders.takeaway.create' => 'User: Create takeaway',
            'orders.takeaway.store' => 'User: Store takeaway',
            'orders.takeaway.summary' => 'User: Takeaway summary',
            
            // Admin Routes
            'admin.orders.index' => 'Admin: List orders',
            'admin.orders.create' => 'Admin: Create order',
            'admin.orders.dashboard' => 'Admin: Order dashboard',
            'admin.orders.takeaway.create' => 'Admin: Create takeaway',
            'admin.orders.takeaway.index' => 'Admin: List takeaway',
            'admin.orders.reservations.index' => 'Admin: List reservation orders',
        ];

        $routeContent = '';
        foreach ($routeFiles as $file) {
            if (file_exists($file)) {
                $routeContent .= file_get_contents($file) . "\n";
            }
        }

        foreach ($requiredRoutes as $routeName => $description) {
            if (strpos($routeContent, "name('{$routeName}')") !== false) {
                echo "  ✅ {$routeName} - {$description}\n";
                $this->results["route:{$routeName}"] = 'EXISTS';
            } else {
                echo "  ❌ {$routeName} - {$description} (MISSING)\n";
                $this->warnings[] = "Route may be missing: {$routeName}";
                $this->results["route:{$routeName}"] = 'MISSING';
            }
        }
        echo "\n";
    }

    private function auditSessionDefaults(): void
    {
        echo "4. SESSION DEFAULTS AUDIT\n";
        echo "==========================\n";

        $adminController = 'app/Http/Controllers/AdminOrderController.php';
        if (file_exists($adminController)) {
            $content = file_get_contents($adminController);
            
            $sessionFeatures = [
                'branch_id' => 'Branch pre-fill from admin session',
                'organization_id' => 'Organization-based filtering',
                'user()->branch_id' => 'User branch detection',
                'auth(\'admin\')->user()' => 'Admin authentication check',
            ];

            foreach ($sessionFeatures as $feature => $description) {
                if (strpos($content, $feature) !== false) {
                    echo "  ✅ {$feature} - {$description}\n";
                    $this->results["session:{$feature}"] = 'IMPLEMENTED';
                } else {
                    echo "  ⚠️  {$feature} - {$description} (NEEDS VERIFICATION)\n";
                    $this->warnings[] = "Session feature needs verification: {$feature}";
                }
            }
        }
        echo "\n";
    }

    private function auditStockValidation(): void
    {
        echo "5. STOCK VALIDATION AUDIT\n";
        echo "==========================\n";

        $files = [
            'app/Http/Controllers/OrderController.php',
            'app/Http/Controllers/AdminOrderController.php',
        ];

        $stockFeatures = [
            'stock.*validation' => 'Stock validation logic',
            'insufficient.*stock' => 'Insufficient stock handling',
            'reserveStock' => 'Stock reservation system',
            'inventory.*check' => 'Inventory checking',
            'ItemTransaction' => 'Stock transaction recording',
        ];

        foreach ($files as $file) {
            if (!file_exists($file)) continue;
            $content = file_get_contents($file);
            
            echo "  Checking {$file}...\n";
            foreach ($stockFeatures as $pattern => $description) {
                if (preg_match("/{$pattern}/i", $content)) {
                    echo "    ✅ {$description}\n";
                    $this->results["stock:{$description}"] = 'IMPLEMENTED';
                } else {
                    echo "    ❌ {$description} (MISSING)\n";
                    $this->errors[] = "Stock feature missing: {$description}";
                }
            }
        }
        echo "\n";
    }

    private function auditTransactionSafety(): void
    {
        echo "6. TRANSACTION SAFETY AUDIT\n";
        echo "============================\n";

        $files = [
            'app/Http/Controllers/OrderController.php',
            'app/Http/Controllers/AdminOrderController.php',
        ];

        $transactionFeatures = [
            'DB::beginTransaction' => 'Database transaction start',
            'DB::commit' => 'Transaction commit',
            'DB::rollBack' => 'Transaction rollback',
            'try.*catch' => 'Error handling wrapper',
        ];

        foreach ($files as $file) {
            if (!file_exists($file)) continue;
            $content = file_get_contents($file);
            
            echo "  Checking {$file}...\n";
            foreach ($transactionFeatures as $pattern => $description) {
                if (preg_match("/{$pattern}/i", $content)) {
                    echo "    ✅ {$description}\n";
                    $this->results["transaction:{$description}"] = 'IMPLEMENTED';
                } else {
                    echo "    ❌ {$description} (MISSING)\n";
                    $this->errors[] = "Transaction safety missing: {$description}";
                }
            }
        }
        echo "\n";
    }

    private function generateReport(): void
    {
        echo "COMPREHENSIVE AUDIT REPORT\n";
        echo "==========================\n\n";

        echo "📊 STATISTICS:\n";
        echo "  • Total Items Checked: " . count($this->results) . "\n";
        echo "  • ✅ Implemented: " . count(array_filter($this->results, fn($v) => in_array($v, ['EXISTS', 'IMPLEMENTED']))) . "\n";
        echo "  • ❌ Missing: " . count(array_filter($this->results, fn($v) => $v === 'MISSING')) . "\n";
        echo "  • ⚠️  Warnings: " . count($this->warnings) . "\n\n";

        if (!empty($this->errors)) {
            echo "🚫 CRITICAL ISSUES TO FIX:\n";
            foreach ($this->errors as $error) {
                echo "  • {$error}\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️  WARNINGS TO REVIEW:\n";
            foreach ($this->warnings as $warning) {
                echo "  • {$warning}\n";
            }
            echo "\n";
        }

        echo "🔧 RECOMMENDED ACTIONS:\n";
        echo "  1. Create missing blade templates\n";
        echo "  2. Implement missing controller methods\n";
        echo "  3. Add missing routes\n";
        echo "  4. Enhance session-based defaults\n";
        echo "  5. Implement stock validation\n";
        echo "  6. Add transaction safety\n\n";

        echo "✅ AUDIT COMPLETE\n";
        echo "Next steps: Implement missing components based on this report.\n";
    }
}

// Run the audit
$audit = new OrderManagementSystemAudit();
$audit->runAudit();
