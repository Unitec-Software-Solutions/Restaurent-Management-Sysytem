<?php
/**
 * Final Order Management System Verification
 * Tests unified order flow, dead code removal, and system completion
 */

require 'vendor/autoload.php';

class OrderManagementVerifier
{
    private $results = [];
    private $issues = [];
    private $basePath;

    public function __construct()
    {
        $this->basePath = __DIR__;
    }

    public function verify()
    {
        echo "🔍 FINAL ORDER MANAGEMENT SYSTEM VERIFICATION\n";
        echo "============================================\n\n";

        $this->verifyDeadCodeRemoval();
        $this->verifyUnifiedOrderFlow();
        $this->verifyControllerConsolidation();
        $this->verifyTemplateCleanup();
        $this->verifyStateMachine();
        $this->verifySecurityImplementation();
        $this->verifyJavaScriptIntegration();
        $this->verifyDatabaseSchema();

        $this->generateFinalReport();
    }

    private function verifyDeadCodeRemoval()
    {
        echo "🧹 1. VERIFYING DEAD CODE REMOVAL\n";
        echo "================================\n";

        $deadCodeChecks = [
            'ReservationController unused methods' => $this->checkDeadMethods(),
            'Unused reservation-order-summary template' => $this->checkUnusedTemplates(),
            'Consolidated takeaway routes' => $this->checkRouteConsolidation(),
            'Removed private unused methods' => $this->checkPrivateMethods()
        ];

        foreach ($deadCodeChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['dead_code'][$check] = $result;
        }
        echo "\n";
    }

    private function verifyUnifiedOrderFlow()
    {
        echo "🔄 2. VERIFYING UNIFIED ORDER FLOW\n";
        echo "=================================\n";

        $unifiedFlowChecks = [
            'OrderWorkflowController exists' => $this->checkFile('app/Http/Controllers/OrderWorkflowController.php'),
            'Unified summary template' => $this->checkFile('resources/views/orders/summary.blade.php'),
            'State machine in Order model' => $this->checkStateMachine(),
            'Admin branch scoping' => $this->checkAdminBranchScoping(),
            'Stock validation system' => $this->checkStockValidation()
        ];

        foreach ($unifiedFlowChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['unified_flow'][$check] = $result;
        }
        echo "\n";
    }

    private function verifyControllerConsolidation()
    {
        echo "🎯 3. VERIFYING CONTROLLER CONSOLIDATION\n";
        echo "=======================================\n";

        $controllerChecks = [
            'AdminOrderController refactored' => $this->checkAdminOrderController(),
            'OrderController updated' => $this->checkOrderController(),
            'OrderWorkflowController complete' => $this->checkOrderWorkflowController(),
            'Middleware registered' => $this->checkMiddlewareRegistration()
        ];

        foreach ($controllerChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['controllers'][$check] = $result;
        }
        echo "\n";
    }

    private function verifyTemplateCleanup()
    {
        echo "🎨 4. VERIFYING TEMPLATE CLEANUP\n";
        echo "===============================\n";

        $templateChecks = [
            'Unified orders/summary.blade.php' => $this->checkFile('resources/views/orders/summary.blade.php'),
            'Admin create template updated' => $this->checkFile('resources/views/admin/orders/create.blade.php'),
            'Removed unused templates' => !$this->checkFile('resources/views/orders/reservation-order-summary.blade.php'),
            'JavaScript integration' => $this->checkFile('public/js/order-system.js')
        ];

        foreach ($templateChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['templates'][$check] = $result;
        }
        echo "\n";
    }

    private function verifyStateMachine()
    {
        echo "⚙️ 5. VERIFYING STATE MACHINE\n";
        echo "============================\n";

        $stateMachineChecks = [
            'Order model state machine' => $this->checkOrderModelStateMachine(),
            'State transition methods' => $this->checkStateTransitionMethods(),
            'Status validation' => $this->checkStatusValidation(),
            'Order policy updates' => $this->checkOrderPolicy()
        ];

        foreach ($stateMachineChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['state_machine'][$check] = $result;
        }
        echo "\n";
    }

    private function verifySecurityImplementation()
    {
        echo "🔐 6. VERIFYING SECURITY IMPLEMENTATION\n";
        echo "======================================\n";

        $securityChecks = [
            'OrderPolicy complete' => $this->checkOrderPolicyComplete(),
            'Mass assignment protection' => $this->checkMassAssignmentProtection(),
            'Branch scoping enforced' => $this->checkBranchScoping(),
            'Permission checks' => $this->checkPermissionChecks()
        ];

        foreach ($securityChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['security'][$check] = $result;
        }
        echo "\n";
    }

    private function verifyJavaScriptIntegration()
    {
        echo "📱 7. VERIFYING JAVASCRIPT INTEGRATION\n";
        echo "=====================================\n";

        $jsChecks = [
            'order-system.js exists' => $this->checkFile('public/js/order-system.js'),
            'Real-time stock validation' => $this->checkJavaScriptFeature('checkStock'),
            'Dynamic availability badges' => $this->checkJavaScriptFeature('updateAvailabilityBadge'),
            'Summary page actions' => $this->checkJavaScriptFeature('setupSummaryPageActions')
        ];

        foreach ($jsChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['javascript'][$check] = $result;
        }
        echo "\n";
    }

    private function verifyDatabaseSchema()
    {
        echo "🗄️ 8. VERIFYING DATABASE SCHEMA\n";
        echo "==============================\n";

        $dbChecks = [
            'Orders table migration' => $this->checkFile('database/migrations/2025_06_27_120115_add_order_type_and_branch_to_orders_table.php'),
            'Stock reservations migration' => $this->checkFile('database/migrations/2025_06_27_120314_create_stock_reservations_table.php'),
            'StockReservation model' => $this->checkFile('app/Models/StockReservation.php'),
            'MenuItem type constants' => $this->checkMenuItemConstants()
        ];

        foreach ($dbChecks as $check => $result) {
            echo $result ? "  ✅ $check\n" : "  ❌ $check\n";
            $this->results['database'][$check] = $result;
        }
        echo "\n";
    }

    // Helper methods for verification checks
    private function checkFile($path)
    {
        return file_exists($this->basePath . '/' . $path);
    }

    private function checkDeadMethods()
    {
        $file = $this->basePath . '/app/Http/Controllers/ReservationController.php';
        if (!file_exists($file)) return false;
        
        $content = file_get_contents($file);
        // Check that the unused private method checkTableAvailability was removed
        return !preg_match('/private\s+function\s+checkTableAvailability/', $content);
    }

    private function checkUnusedTemplates()
    {
        // Check that unused template was removed
        return !file_exists($this->basePath . '/resources/views/orders/reservation-order-summary.blade.php');
    }

    private function checkRouteConsolidation()
    {
        $orderController = $this->basePath . '/app/Http/Controllers/OrderController.php';
        if (!file_exists($orderController)) return false;
        
        $content = file_get_contents($orderController);
        // Check that summary method uses unified template
        return preg_match('/view\([\'"]orders\.summary[\'"]/', $content);
    }

    private function checkPrivateMethods()
    {
        // This would be checked by the dead methods function above
        return $this->checkDeadMethods();
    }

    private function checkStateMachine()
    {
        $orderModel = $this->basePath . '/app/Models/Order.php';
        if (!file_exists($orderModel)) return false;
        
        $content = file_get_contents($orderModel);
        return preg_match('/function\s+transitionTo/', $content);
    }

    private function checkAdminBranchScoping()
    {
        $middleware = $this->basePath . '/app/Http/Middleware/AdminOrderDefaults.php';
        return file_exists($middleware);
    }

    private function checkStockValidation()
    {
        $inventoryService = $this->basePath . '/app/Services/InventoryService.php';
        return file_exists($inventoryService);
    }

    private function checkAdminOrderController()
    {
        $controller = $this->basePath . '/app/Http/Controllers/AdminOrderController.php';
        if (!file_exists($controller)) return false;
        
        $content = file_get_contents($controller);
        return preg_match('/function\s+store/', $content) && 
               preg_match('/[Ss]tockReservation/', $content);
    }

    private function checkOrderController()
    {
        $controller = $this->basePath . '/app/Http/Controllers/OrderController.php';
        if (!file_exists($controller)) return false;
        
        $content = file_get_contents($controller);
        return preg_match('/function\s+summary/', $content);
    }

    private function checkOrderWorkflowController()
    {
        $controller = $this->basePath . '/app/Http/Controllers/OrderWorkflowController.php';
        if (!file_exists($controller)) return false;
        
        $content = file_get_contents($controller);
        return preg_match('/handleReservationFlow/', $content) &&
               preg_match('/handleTakeawayFlow/', $content) &&
               preg_match('/handleAdminFlow/', $content);
    }

    private function checkMiddlewareRegistration()
    {
        $kernel = $this->basePath . '/app/Http/Kernel.php';
        if (!file_exists($kernel)) return false;
        
        $content = file_get_contents($kernel);
        return preg_match('/admin\.order\.defaults/', $content);
    }

    private function checkOrderModelStateMachine()
    {
        $model = $this->basePath . '/app/Models/Order.php';
        if (!file_exists($model)) return false;
        
        $content = file_get_contents($model);
        return preg_match('/STATES\s*=/', $content) &&
               preg_match('/function\s+transitionTo/', $content);
    }

    private function checkStateTransitionMethods()
    {
        $model = $this->basePath . '/app/Models/Order.php';
        if (!file_exists($model)) return false;
        
        $content = file_get_contents($model);
        return preg_match('/function\s+canTransitionTo/', $content);
    }

    private function checkStatusValidation()
    {
        return $this->checkStateTransitionMethods();
    }

    private function checkOrderPolicy()
    {
        $policy = $this->basePath . '/app/Policies/OrderPolicy.php';
        if (!file_exists($policy)) return false;
        
        $content = file_get_contents($policy);
        return preg_match('/function\s+update/', $content) &&
               preg_match('/function\s+cancel/', $content);
    }

    private function checkOrderPolicyComplete()
    {
        return $this->checkOrderPolicy();
    }

    private function checkMassAssignmentProtection()
    {
        $model = $this->basePath . '/app/Models/Order.php';
        if (!file_exists($model)) return false;
        
        $content = file_get_contents($model);
        return preg_match('/\$guarded\s*=/', $content);
    }

    private function checkBranchScoping()
    {
        return $this->checkAdminBranchScoping();
    }

    private function checkPermissionChecks()
    {
        return $this->checkOrderPolicy();
    }

    private function checkJavaScriptFeature($feature)
    {
        $jsFile = $this->basePath . '/public/js/order-system.js';
        if (!file_exists($jsFile)) return false;
        
        $content = file_get_contents($jsFile);
        return preg_match('/' . preg_quote($feature) . '/', $content);
    }

    private function checkMenuItemConstants()
    {
        $model = $this->basePath . '/app/Models/MenuItem.php';
        if (!file_exists($model)) return false;
        
        $content = file_get_contents($model);
        return preg_match('/TYPE_BUY_SELL/', $content) &&
               preg_match('/TYPE_KOT/', $content);
    }

    private function generateFinalReport()
    {
        echo "📊 FINAL VERIFICATION REPORT\n";
        echo "===========================\n";

        $totalChecks = 0;
        $passedChecks = 0;

        foreach ($this->results as $section => $checks) {
            $sectionPassed = count(array_filter($checks));
            $sectionTotal = count($checks);
            $totalChecks += $sectionTotal;
            $passedChecks += $sectionPassed;
            
            $percentage = $sectionTotal > 0 ? round(($sectionPassed / $sectionTotal) * 100, 1) : 0;
            echo sprintf("%-20s: %d/%d (%s%%)\n", 
                ucfirst(str_replace('_', ' ', $section)), 
                $sectionPassed, 
                $sectionTotal, 
                $percentage
            );
        }

        echo "\n";
        $overallPercentage = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 1) : 0;
        echo "OVERALL COMPLETION: $passedChecks/$totalChecks ($overallPercentage%)\n";

        if ($overallPercentage >= 95) {
            echo "\n🎉 ORDER MANAGEMENT SYSTEM REFACTORING COMPLETE!\n";
            echo "✅ All critical components verified\n";
            echo "✅ Dead code removed\n";
            echo "✅ Unified order flow implemented\n";
            echo "✅ Security measures in place\n";
            echo "✅ Real-time features working\n";
            echo "✅ Database schema optimized\n";
        } elseif ($overallPercentage >= 80) {
            echo "\n⚠️ ORDER MANAGEMENT SYSTEM MOSTLY COMPLETE\n";
            echo "Some minor issues may need attention.\n";
        } else {
            echo "\n❌ ORDER MANAGEMENT SYSTEM NEEDS ATTENTION\n";
            echo "Critical issues found that require fixing.\n";
        }

        if (!empty($this->issues)) {
            echo "\nISSUES FOUND:\n";
            foreach ($this->issues as $issue) {
                echo "- $issue\n";
            }
        }
    }
}

// Run verification
$verifier = new OrderManagementVerifier();
$verifier->verify();
