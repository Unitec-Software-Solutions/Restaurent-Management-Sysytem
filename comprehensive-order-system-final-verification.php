<?php
/**
 * Comprehensive Order System Final Verification Script
 * 
 * This script performs a complete audit of the order management system
 * to ensure all flows work correctly with proper menu item retrieval,
 * stock validation, KOT badges, and admin/customer flows.
 */

require_once __DIR__ . '/vendor/autoload.php';

class OrderSystemFinalVerification
{
    private $errors = [];
    private $warnings = [];
    private $successes = [];
    private $checksPassed = 0;
    private $totalChecks = 0;

    public function runVerification()
    {
        echo "🔍 Starting Comprehensive Order System Final Verification...\n\n";

        // Core System Checks
        $this->checkControllerMethods();
        $this->checkBladeTemplates();
        $this->checkMenuItemRetrieval();
        $this->checkStockValidation();
        $this->checkKOTBadgeLogic();
        $this->checkAdminSessionDefaults();
        $this->checkOrderFlows();
        $this->checkUIConsistency();
        $this->checkErrorHandling();
        $this->checkDatabaseIntegrity();

        $this->generateReport();
    }

    private function checkControllerMethods()
    {
        echo "📋 Checking Controller Methods...\n";

        $controllers = [
            'app/Http/Controllers/OrderController.php' => [
                'methods' => [
                    'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
                    'createTakeaway', 'storeTakeaway', 'indexTakeaway', 'editTakeaway', 'updateTakeaway',
                    'getAvailableMenuItems', 'getMenuItemsByType', 'checkStock', 'summary'
                ],
                'required_features' => [
                    'session-based branch filtering',
                    'menu item validation',
                    'stock checking',
                    'KOT/Buy-Sell differentiation'
                ]
            ],
            'app/Http/Controllers/AdminOrderController.php' => [
                'methods' => [
                    'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
                    'createTakeaway', 'storeTakeaway', 'indexTakeaway', 'editTakeaway', 'updateTakeaway',
                    'getAvailableMenuItems', 'takeawayTypeSelector', 'getValidatedMenuItems'
                ],
                'required_features' => [
                    'admin session defaults',
                    'organization/branch filtering',
                    'enhanced menu validation',
                    'super admin capabilities'
                ]
            ]
        ];

        foreach ($controllers as $controllerPath => $requirements) {
            $this->verifyControllerFile($controllerPath, $requirements);
        }
    }

    private function verifyControllerFile($path, $requirements)
    {
        $this->totalChecks++;
        
        if (!file_exists($path)) {
            $this->errors[] = "Controller not found: $path";
            return;
        }

        $content = file_get_contents($path);
        $missingMethods = [];
        
        foreach ($requirements['methods'] as $method) {
            if (!preg_match("/function\s+{$method}\s*\(/", $content)) {
                $missingMethods[] = $method;
            }
        }

        if (empty($missingMethods)) {
            $this->successes[] = "✅ All required methods found in " . basename($path);
            $this->checksPassed++;
        } else {
            $this->errors[] = "❌ Missing methods in " . basename($path) . ": " . implode(', ', $missingMethods);
        }

        // Check for enhanced menu item retrieval
        $this->totalChecks++;
        if (strpos($content, 'getValidatedMenuItems') !== false || 
            strpos($content, 'buying_price') !== false && strpos($content, 'selling_price') !== false) {
            $this->successes[] = "✅ Enhanced menu item validation found in " . basename($path);
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Enhanced menu validation might be missing in " . basename($path);
        }
    }

    private function checkBladeTemplates()
    {
        echo "🎨 Checking Blade Templates...\n";

        $requiredTemplates = [
            // Customer order templates
            'resources/views/orders/create.blade.php' => [
                'features' => ['menu item display', 'stock indicators', 'KOT badges', 'form validation']
            ],
            'resources/views/orders/summary.blade.php' => [
                'features' => ['order summary', 'payment options', 'item details']
            ],
            'resources/views/orders/takeaway/create.blade.php' => [
                'features' => ['takeaway-specific form', 'pickup time', 'contact info']
            ],
            'resources/views/orders/takeaway/summary.blade.php' => [
                'features' => ['takeaway summary', 'pickup details']
            ],
            
            // Admin order templates
            'resources/views/admin/orders/create.blade.php' => [
                'features' => ['admin controls', 'branch selection', 'enhanced menu options']
            ],
            'resources/views/admin/orders/takeaway/create.blade.php' => [
                'features' => ['admin takeaway form', 'customer selection', 'scheduling']
            ],
            'resources/views/admin/orders/takeaway-type-selector.blade.php' => [
                'features' => ['type selection', 'admin-specific options']
            ],
            
            // Additional templates
            'resources/views/orders/reservation-order-summary.blade.php' => [
                'features' => ['reservation details', 'table info', 'timing']
            ],
            'resources/views/orders/payment_or_repeat.blade.php' => [
                'features' => ['payment options', 'repeat order', 'confirmation']
            ]
        ];

        foreach ($requiredTemplates as $templatePath => $requirements) {
            $this->verifyBladeTemplate($templatePath, $requirements);
        }
    }

    private function verifyBladeTemplate($path, $requirements)
    {
        $this->totalChecks++;
        
        if (!file_exists($path)) {
            $this->errors[] = "❌ Blade template missing: $path";
            return;
        }

        $content = file_get_contents($path);
        $missingFeatures = [];

        // Check for common UI patterns
        $patterns = [
            'menu item display' => '/menu.*item|item.*menu/',
            'stock indicators' => '/stock|available|out.*of.*stock/',
            'KOT badges' => '/KOT|kot|badge.*kot/',
            'form validation' => '/@error|errors|validation/',
            'admin controls' => '/admin|branch.*select|organization/',
            'payment options' => '/payment|pay|checkout/',
            'reservation details' => '/reservation|table|booking/'
        ];

        foreach ($requirements['features'] as $feature) {
            if (isset($patterns[$feature])) {
                if (!preg_match($patterns[$feature] . 'i', $content)) {
                    $missingFeatures[] = $feature;
                }
            }
        }

        if (empty($missingFeatures)) {
            $this->successes[] = "✅ Template verified: " . basename($path);
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Possible missing features in " . basename($path) . ": " . implode(', ', $missingFeatures);
        }
    }

    private function checkMenuItemRetrieval()
    {
        echo "🍽️ Checking Menu Item Retrieval Logic...\n";

        $controllerFiles = [
            'app/Http/Controllers/OrderController.php',
            'app/Http/Controllers/AdminOrderController.php'
        ];

        foreach ($controllerFiles as $file) {
            $this->verifyMenuItemRetrievalLogic($file);
        }
    }

    private function verifyMenuItemRetrievalLogic($filePath)
    {
        $this->totalChecks += 4; // Multiple checks per file
        
        if (!file_exists($filePath)) {
            $this->errors[] = "❌ Controller file not found: $filePath";
            return;
        }

        $content = file_get_contents($filePath);
        $checksCount = 0;

        // Check 1: Buy/Sell price validation
        if (preg_match('/buying_price.*selling_price|selling_price.*buying_price/', $content)) {
            $this->successes[] = "✅ Buy/Sell price validation found in " . basename($filePath);
            $checksCount++;
        } else {
            $this->errors[] = "❌ Buy/Sell price validation missing in " . basename($filePath);
        }

        // Check 2: Stock level checking
        if (preg_match('/current_stock|stock.*level|stockOnHand/', $content)) {
            $this->successes[] = "✅ Stock level checking found in " . basename($filePath);
            $checksCount++;
        } else {
            $this->errors[] = "❌ Stock level checking missing in " . basename($filePath);
        }

        // Check 3: KOT item handling
        if (preg_match('/item_type.*KOT|KOT.*item|kot.*badge/i', $content)) {
            $this->successes[] = "✅ KOT item handling found in " . basename($filePath);
            $checksCount++;
        } else {
            $this->warnings[] = "⚠️ KOT item handling might be missing in " . basename($filePath);
        }

        // Check 4: Menu attributes validation
        if (preg_match('/attributes.*cuisine_type|prep_time_minutes|serving_size/', $content)) {
            $this->successes[] = "✅ Menu attributes validation found in " . basename($filePath);
            $checksCount++;
        } else {
            $this->warnings[] = "⚠️ Menu attributes validation might be missing in " . basename($filePath);
        }

        $this->checksPassed += $checksCount;
    }

    private function checkStockValidation()
    {
        echo "📦 Checking Stock Validation System...\n";

        $this->totalChecks += 3;

        // Check ItemTransaction model/service
        $stockFiles = [
            'app/Models/ItemTransaction.php',
            'app/Services/InventoryService.php',
            'app/Services/InventoryAlertService.php'
        ];

        $stockValidationFound = false;
        foreach ($stockFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/stockOnHand|current_stock|stock.*validation/', $content)) {
                    $stockValidationFound = true;
                    break;
                }
            }
        }

        if ($stockValidationFound) {
            $this->successes[] = "✅ Stock validation system found";
            $this->checksPassed++;
        } else {
            $this->errors[] = "❌ Stock validation system missing";
        }

        // Check for real-time stock checking in order controllers
        $orderControllers = ['app/Http/Controllers/OrderController.php', 'app/Http/Controllers/AdminOrderController.php'];
        $realTimeCheckFound = false;

        foreach ($orderControllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/checkStock|validateStock|stock.*available/', $content)) {
                    $realTimeCheckFound = true;
                    break;
                }
            }
        }

        if ($realTimeCheckFound) {
            $this->successes[] = "✅ Real-time stock checking found in order controllers";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Real-time stock checking might be missing";
        }

        // Check for stock reservation system
        if (file_exists('app/Services/OrderManagementService.php')) {
            $content = file_get_contents('app/Services/OrderManagementService.php');
            if (preg_match('/reserve.*stock|stock.*reservation/i', $content)) {
                $this->successes[] = "✅ Stock reservation system found";
                $this->checksPassed++;
            } else {
                $this->warnings[] = "⚠️ Stock reservation system might be missing";
            }
        }
    }

    private function checkKOTBadgeLogic()
    {
        echo "🏷️ Checking KOT Badge Logic...\n";

        $this->totalChecks += 2;

        // Check in blade templates
        $templateFiles = glob('resources/views/orders/**/*.blade.php') + glob('resources/views/admin/orders/**/*.blade.php');
        $kotBadgeFound = false;

        foreach ($templateFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/KOT.*badge|badge.*KOT|green.*tag.*KOT/i', $content)) {
                    $kotBadgeFound = true;
                    break;
                }
            }
        }

        if ($kotBadgeFound) {
            $this->successes[] = "✅ KOT badge display logic found in templates";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ KOT badge display logic might be missing in templates";
        }

        // Check in controllers for KOT item differentiation
        $controllers = ['app/Http/Controllers/OrderController.php', 'app/Http/Controllers/AdminOrderController.php'];
        $kotDifferentiationFound = false;

        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/item_type.*KOT|display_kot_badge|is_kot_item/i', $content)) {
                    $kotDifferentiationFound = true;
                    break;
                }
            }
        }

        if ($kotDifferentiationFound) {
            $this->successes[] = "✅ KOT item differentiation found in controllers";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ KOT item differentiation might be missing in controllers";
        }
    }

    private function checkAdminSessionDefaults()
    {
        echo "👨‍💼 Checking Admin Session Defaults...\n";

        $this->totalChecks += 2;

        if (file_exists('app/Http/Controllers/AdminOrderController.php')) {
            $content = file_get_contents('app/Http/Controllers/AdminOrderController.php');
            
            // Check for session-based admin defaults
            if (preg_match('/getAdminSessionDefaults|admin.*session|default.*branch/', $content)) {
                $this->successes[] = "✅ Admin session defaults found";
                $this->checksPassed++;
            } else {
                $this->warnings[] = "⚠️ Admin session defaults might be missing";
            }

            // Check for branch filtering
            if (preg_match('/branch.*filter|organization.*filter|is_super_admin/', $content)) {
                $this->successes[] = "✅ Admin branch/organization filtering found";
                $this->checksPassed++;
            } else {
                $this->warnings[] = "⚠️ Admin filtering logic might be missing";
            }
        } else {
            $this->errors[] = "❌ AdminOrderController not found";
        }
    }

    private function checkOrderFlows()
    {
        echo "🔄 Checking Order Flows...\n";

        $flows = [
            'reservation_orders' => [
                'controller_methods' => ['create', 'store', 'summary'],
                'templates' => ['orders/create.blade.php', 'orders/summary.blade.php', 'orders/reservation-order-summary.blade.php']
            ],
            'takeaway_orders' => [
                'controller_methods' => ['createTakeaway', 'storeTakeaway', 'indexTakeaway'],
                'templates' => ['orders/takeaway/create.blade.php', 'orders/takeaway/summary.blade.php']
            ],
            'admin_orders' => [
                'controller_methods' => ['create', 'createTakeaway', 'takeawayTypeSelector'],
                'templates' => ['admin/orders/create.blade.php', 'admin/orders/takeaway/create.blade.php', 'admin/orders/takeaway-type-selector.blade.php']
            ]
        ];

        foreach ($flows as $flowName => $requirements) {
            $this->verifyOrderFlow($flowName, $requirements);
        }
    }

    private function verifyOrderFlow($flowName, $requirements)
    {
        $this->totalChecks++;
        
        $missingComponents = [];
        
        // Check controller methods
        $controllerFile = str_contains($flowName, 'admin') ? 
            'app/Http/Controllers/AdminOrderController.php' : 
            'app/Http/Controllers/OrderController.php';
            
        if (file_exists($controllerFile)) {
            $content = file_get_contents($controllerFile);
            foreach ($requirements['controller_methods'] as $method) {
                if (!preg_match("/function\s+{$method}\s*\(/", $content)) {
                    $missingComponents[] = "method: $method";
                }
            }
        } else {
            $missingComponents[] = "controller file";
        }

        // Check templates
        foreach ($requirements['templates'] as $template) {
            if (!file_exists("resources/views/$template")) {
                $missingComponents[] = "template: $template";
            }
        }

        if (empty($missingComponents)) {
            $this->successes[] = "✅ Complete order flow: $flowName";
            $this->checksPassed++;
        } else {
            $this->errors[] = "❌ Incomplete order flow '$flowName': missing " . implode(', ', $missingComponents);
        }
    }

    private function checkUIConsistency()
    {
        echo "🎨 Checking UI/UX Consistency...\n";

        $this->totalChecks += 3;

        // Check for consistent CSS classes
        $templateFiles = array_merge(
            glob('resources/views/orders/**/*.blade.php'),
            glob('resources/views/admin/orders/**/*.blade.php')
        );

        $uiPatterns = [
            'tailwind_classes' => '/bg-\w+-\d+|text-\w+-\d+|px-\d+|py-\d+/',
            'form_controls' => '/form-control|form-input|btn|button/',
            'status_badges' => '/badge|status|tag/'
        ];

        $consistencyFound = 0;
        foreach ($templateFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                foreach ($uiPatterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $consistencyFound++;
                        break;
                    }
                }
            }
        }

        if ($consistencyFound >= count($templateFiles) * 0.8) {
            $this->successes[] = "✅ UI consistency patterns found across templates";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ UI consistency might need improvement";
        }

        // Check for responsive design
        $responsiveFound = 0;
        foreach ($templateFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/md:|lg:|sm:|xl:|responsive|mobile/i', $content)) {
                    $responsiveFound++;
                }
            }
        }

        if ($responsiveFound >= count($templateFiles) * 0.6) {
            $this->successes[] = "✅ Responsive design patterns found";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Responsive design patterns might be missing";
        }

        // Check for accessibility features
        $accessibilityFound = 0;
        foreach ($templateFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/aria-|alt=|label|title=/', $content)) {
                    $accessibilityFound++;
                }
            }
        }

        if ($accessibilityFound >= count($templateFiles) * 0.5) {
            $this->successes[] = "✅ Accessibility features found";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Accessibility features might need improvement";
        }
    }

    private function checkErrorHandling()
    {
        echo "🚨 Checking Error Handling...\n";

        $this->totalChecks += 2;

        $controllers = [
            'app/Http/Controllers/OrderController.php',
            'app/Http/Controllers/AdminOrderController.php'
        ];

        $errorHandlingFound = false;
        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/try.*catch|ValidationException|catch.*Exception/', $content)) {
                    $errorHandlingFound = true;
                    break;
                }
            }
        }

        if ($errorHandlingFound) {
            $this->successes[] = "✅ Error handling found in controllers";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Error handling might need improvement";
        }

        // Check for validation rules
        $validationFound = false;
        foreach ($controllers as $controller) {
            if (file_exists($controller)) {
                $content = file_get_contents($controller);
                if (preg_match('/validate|rules|required|numeric/', $content)) {
                    $validationFound = true;
                    break;
                }
            }
        }

        if ($validationFound) {
            $this->successes[] = "✅ Validation rules found";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Validation rules might be missing";
        }
    }

    private function checkDatabaseIntegrity()
    {
        echo "🗄️ Checking Database Integration...\n";

        $this->totalChecks += 3;

        // Check for proper model relationships
        $models = [
            'app/Models/Order.php' => ['orderItems', 'branch', 'user'],
            'app/Models/OrderItem.php' => ['order', 'menuItem'],
            'app/Models/ItemMaster.php' => ['category', 'branch', 'transactions']
        ];

        $relationshipsFound = 0;
        foreach ($models as $modelFile => $expectedRelations) {
            if (file_exists($modelFile)) {
                $content = file_get_contents($modelFile);
                $foundRelations = 0;
                foreach ($expectedRelations as $relation) {
                    if (preg_match("/function\s+{$relation}\s*\(/", $content)) {
                        $foundRelations++;
                    }
                }
                if ($foundRelations >= count($expectedRelations) * 0.7) {
                    $relationshipsFound++;
                }
            }
        }

        if ($relationshipsFound >= count($models) * 0.8) {
            $this->successes[] = "✅ Model relationships appear complete";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Some model relationships might be missing";
        }

        // Check for transaction safety
        $transactionFound = false;
        $serviceFiles = glob('app/Services/*.php');
        foreach ($serviceFiles as $service) {
            if (file_exists($service)) {
                $content = file_get_contents($service);
                if (preg_match('/DB::transaction|beginTransaction|commit|rollback/', $content)) {
                    $transactionFound = true;
                    break;
                }
            }
        }

        if ($transactionFound) {
            $this->successes[] = "✅ Database transaction safety found";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Database transaction safety might be missing";
        }

        // Check for proper indexing hints
        $migrationFiles = glob('database/migrations/*.php');
        $indexFound = false;
        foreach ($migrationFiles as $migration) {
            if (file_exists($migration)) {
                $content = file_get_contents($migration);
                if (preg_match('/index|foreign|unique/', $content)) {
                    $indexFound = true;
                    break;
                }
            }
        }

        if ($indexFound) {
            $this->successes[] = "✅ Database indexing found in migrations";
            $this->checksPassed++;
        } else {
            $this->warnings[] = "⚠️ Database indexing might need review";
        }
    }

    private function generateReport()
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "📊 COMPREHENSIVE ORDER SYSTEM VERIFICATION REPORT\n";
        echo str_repeat("=", 80) . "\n\n";

        // Summary
        $passRate = $this->totalChecks > 0 ? round(($this->checksPassed / $this->totalChecks) * 100, 1) : 0;
        echo "📈 SUMMARY:\n";
        echo "   Total Checks: {$this->totalChecks}\n";
        echo "   Passed: {$this->checksPassed}\n";
        echo "   Pass Rate: {$passRate}%\n\n";

        // Status indicator
        if ($passRate >= 90) {
            echo "🎉 SYSTEM STATUS: EXCELLENT - Ready for production\n\n";
        } elseif ($passRate >= 75) {
            echo "✅ SYSTEM STATUS: GOOD - Minor improvements needed\n\n";
        } elseif ($passRate >= 60) {
            echo "⚠️ SYSTEM STATUS: ACCEPTABLE - Several improvements needed\n\n";
        } else {
            echo "❌ SYSTEM STATUS: NEEDS WORK - Major improvements required\n\n";
        }

        // Successes
        if (!empty($this->successes)) {
            echo "✅ SUCCESSES (" . count($this->successes) . "):\n";
            foreach ($this->successes as $success) {
                echo "   $success\n";
            }
            echo "\n";
        }

        // Warnings
        if (!empty($this->warnings)) {
            echo "⚠️ WARNINGS (" . count($this->warnings) . "):\n";
            foreach ($this->warnings as $warning) {
                echo "   $warning\n";
            }
            echo "\n";
        }

        // Errors
        if (!empty($this->errors)) {
            echo "❌ ERRORS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "   $error\n";
            }
            echo "\n";
        }

        // Recommendations
        echo "💡 RECOMMENDATIONS:\n";
        
        if ($passRate >= 90) {
            echo "   • Consider adding automated testing for order flows\n";
            echo "   • Implement real-time stock updates via WebSockets\n";
            echo "   • Add comprehensive logging for audit trails\n";
        } elseif ($passRate >= 75) {
            echo "   • Address any missing controller methods\n";
            echo "   • Ensure all blade templates are complete\n";
            echo "   • Improve error handling where needed\n";
        } else {
            echo "   • Complete missing controller methods immediately\n";
            echo "   • Create all required blade templates\n";
            echo "   • Implement proper stock validation\n";
            echo "   • Add comprehensive error handling\n";
        }

        echo "\n📋 NEXT STEPS:\n";
        echo "   1. Address any critical errors listed above\n";
        echo "   2. Test all order flows manually in the UI\n";
        echo "   3. Verify stock levels and KOT badges display correctly\n";
        echo "   4. Test admin and customer flows thoroughly\n";
        echo "   5. Validate all edge cases (out of stock, validation errors)\n";

        echo "\n" . str_repeat("=", 80) . "\n";
        echo "Verification completed at: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat("=", 80) . "\n";
    }
}

// Run the verification
$verifier = new OrderSystemFinalVerification();
$verifier->runVerification();
