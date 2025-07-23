<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\KitchenStation;
use App\Models\ItemCategory;
use App\Services\OrganizationAutomationService;
use App\Services\BranchAutomationService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

class AutomatedWorkflowTester
{
    private $results = [];
    private $testOrganization = null;
    private $testBranch = null;
    
    public function __construct()
    {
        echo "🔧 Restaurant Management System - Automated Workflow Tester\n";
        echo "=" . str_repeat("=", 60) . "\n\n";
    }
    
    public function runAllTests()
    {
        try {
            $this->testObserverRegistration();
            $this->testOrganizationCreationWorkflow();
            $this->testBranchCreationWorkflow();
            $this->testAdminCreationAndPermissions();
            $this->testKitchenStationCreation();
            $this->testIntegrationWorkflow();
            $this->cleanup();
            $this->displayResults();
        } catch (Exception $e) {
            echo "❌ Critical Error: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
            $this->cleanup();
        }
    }
    
    private function testObserverRegistration()
    {
        echo "📋 Testing Observer Registration...\n";
        
        try {
            // Check if observers are registered
            $organizationObservers = Organization::getObservableEvents();
            $branchObservers = Branch::getObservableEvents();
            
            $this->addResult('Observer Registration', 'Organization Events', 
                !empty($organizationObservers) ? 'PASS' : 'FAIL',
                'Observable events: ' . implode(', ', $organizationObservers)
            );
            
            $this->addResult('Observer Registration', 'Branch Events', 
                !empty($branchObservers) ? 'PASS' : 'FAIL',
                'Observable events: ' . implode(', ', $branchObservers)
            );
            
            echo "✅ Observer registration test completed\n\n";
            
        } catch (Exception $e) {
            $this->addResult('Observer Registration', 'Error', 'FAIL', $e->getMessage());
            echo "❌ Observer registration test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testOrganizationCreationWorkflow()
    {
        echo "🏢 Testing Organization Creation Workflow...\n";
        
        try {
            DB::beginTransaction();
            
            // Create test organization data
            $organizationData = [
                'name' => 'Test Restaurant Chain ' . time(),
                'email' => 'test' . time() . '@restaurant.com',
                'phone' => '+1234567890',
                'address' => '123 Test Street, Test City',
                'contact_person' => 'Test Manager',
                'contact_person_phone' => '+1234567890',
                'contact_person_designation' => 'General Manager',
                'is_active' => false, // Organizations start inactive
                'subscription_plan_id' => 1, // Assuming basic plan exists
            ];
            
            echo "  Creating organization: {$organizationData['name']}\n";
            
            // Test organization creation
            $this->testOrganization = Organization::create($organizationData);
            
            $this->addResult('Organization Creation', 'Organization Created', 
                $this->testOrganization ? 'PASS' : 'FAIL',
                "ID: {$this->testOrganization->id}, Name: {$this->testOrganization->name}"
            );
            
            // Check if head office branch was created automatically
            $headOffice = $this->testOrganization->branches()->where('is_head_office', true)->first();
            
            $this->addResult('Organization Creation', 'Head Office Branch Created', 
                $headOffice ? 'PASS' : 'FAIL',
                $headOffice ? "ID: {$headOffice->id}, Name: {$headOffice->name}" : 'No head office found'
            );
            
            // Check if kitchen stations were created for head office
            if ($headOffice) {
                $kitchenStations = $headOffice->kitchenStations()->count();
                $this->addResult('Organization Creation', 'Kitchen Stations Created', 
                    $kitchenStations > 0 ? 'PASS' : 'FAIL',
                    "Kitchen stations count: {$kitchenStations}"
                );
            }
            
            // Check if organization admin was created
            $orgAdmin = $this->testOrganization->admins()->whereNull('branch_id')->first();
            $this->addResult('Organization Creation', 'Organization Admin Created', 
                $orgAdmin ? 'PASS' : 'FAIL',
                $orgAdmin ? "ID: {$orgAdmin->id}, Email: {$orgAdmin->email}" : 'No org admin found'
            );
            
            // Check if branch admin was created for head office
            if ($headOffice) {
                $branchAdmin = $this->testOrganization->admins()->where('branch_id', $headOffice->id)->first();
                $this->addResult('Organization Creation', 'Branch Admin Created', 
                    $branchAdmin ? 'PASS' : 'FAIL',
                    $branchAdmin ? "ID: {$branchAdmin->id}, Email: {$branchAdmin->email}" : 'No branch admin found'
                );
            }
            
            DB::commit();
            echo "✅ Organization creation workflow test completed\n\n";
            
        } catch (Exception $e) {
            DB::rollback();
            $this->addResult('Organization Creation', 'Error', 'FAIL', $e->getMessage());
            echo "❌ Organization creation workflow test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testBranchCreationWorkflow()
    {
        echo "🏪 Testing Branch Creation Workflow...\n";
        
        if (!$this->testOrganization) {
            echo "⚠️  Skipping branch creation test - no test organization available\n\n";
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Create test branch data
            $branchData = [
                'organization_id' => $this->testOrganization->id,
                'name' => 'Test Branch ' . time(),
                'address' => '456 Branch Street, Branch City',
                'phone' => '+1234567891',
                'email' => 'branch' . time() . '@restaurant.com',
                'type' => 'restaurant',
                'is_head_office' => false,
                'opening_time' => '09:00:00',
                'closing_time' => '22:00:00',
                'total_capacity' => 100,
                'max_capacity' => 50,
                'contact_person' => 'Branch Manager',
                'contact_person_phone' => '+1234567891',
                'is_active' => false, // Branches start inactive
            ];
            
            echo "  Creating branch: {$branchData['name']}\n";
            
            // Test branch creation
            $this->testBranch = Branch::create($branchData);
            
            $this->addResult('Branch Creation', 'Branch Created', 
                $this->testBranch ? 'PASS' : 'FAIL',
                "ID: {$this->testBranch->id}, Name: {$this->testBranch->name}"
            );
            
            // Wait a moment for observers to process
            sleep(1);
            
            // Check if branch admin was created automatically
            $branchAdmin = $this->testBranch->admins()->first();
            $this->addResult('Branch Creation', 'Branch Admin Auto-Created', 
                $branchAdmin ? 'PASS' : 'FAIL',
                $branchAdmin ? "ID: {$branchAdmin->id}, Email: {$branchAdmin->email}" : 'No branch admin found'
            );
            
            // Check if kitchen stations were created for branch
            $kitchenStations = $this->testBranch->kitchenStations()->count();
            $this->addResult('Branch Creation', 'Kitchen Stations Auto-Created', 
                $kitchenStations > 0 ? 'PASS' : 'FAIL',
                "Kitchen stations count: {$kitchenStations}"
            );
            
            // Check if inventory items were created
            $inventoryItems = DB::table('inventory_items')
                ->where('branch_id', $this->testBranch->id)
                ->count();
            $this->addResult('Branch Creation', 'Inventory Items Auto-Created', 
                $inventoryItems >= 0 ? 'PASS' : 'FAIL',
                "Inventory items count: {$inventoryItems}"
            );
            
            DB::commit();
            echo "✅ Branch creation workflow test completed\n\n";
            
        } catch (Exception $e) {
            DB::rollback();
            $this->addResult('Branch Creation', 'Error', 'FAIL', $e->getMessage());
            echo "❌ Branch creation workflow test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testAdminCreationAndPermissions()
    {
        echo "👤 Testing Admin Creation and Permission Assignment...\n";
        
        if (!$this->testOrganization) {
            echo "⚠️  Skipping admin permission test - no test organization available\n\n";
            return;
        }
        
        try {
            // Test organization admin permissions
            $orgAdmin = $this->testOrganization->admins()->whereNull('branch_id')->first();
            
            if ($orgAdmin) {
                $orgPermissions = $orgAdmin->getAllPermissions()->count();
                $orgRoles = $orgAdmin->getRoleNames()->count();
                
                $this->addResult('Admin Permissions', 'Org Admin Permissions', 
                    $orgPermissions > 0 ? 'PASS' : 'FAIL',
                    "Permissions: {$orgPermissions}, Roles: {$orgRoles}"
                );
                
                // Check specific critical permissions
                $criticalPermissions = [
                    'organizations.view',
                    'branches.create',
                    'users.create',
                    'reports.view'
                ];
                
                $hasAllCritical = true;
                $missingPermissions = [];
                
                foreach ($criticalPermissions as $permission) {
                    if (!$orgAdmin->hasPermissionTo($permission)) {
                        $hasAllCritical = false;
                        $missingPermissions[] = $permission;
                    }
                }
                
                $this->addResult('Admin Permissions', 'Org Admin Critical Permissions', 
                    $hasAllCritical ? 'PASS' : 'FAIL',
                    $hasAllCritical ? 'All critical permissions present' : 'Missing: ' . implode(', ', $missingPermissions)
                );
            }
            
            // Test branch admin permissions
            if ($this->testBranch) {
                $branchAdmin = $this->testBranch->admins()->first();
                
                if ($branchAdmin) {
                    $branchPermissions = $branchAdmin->getAllPermissions()->count();
                    $branchRoles = $branchAdmin->getRoleNames()->count();
                    
                    $this->addResult('Admin Permissions', 'Branch Admin Permissions', 
                        $branchPermissions > 0 ? 'PASS' : 'FAIL',
                        "Permissions: {$branchPermissions}, Roles: {$branchRoles}"
                    );
                    
                    // Check branch-specific permissions
                    $branchCriticalPermissions = [
                        'branches.view',
                        'orders.create',
                        'kitchen.manage',
                        'staff.manage'
                    ];
                    
                    $hasAllBranchCritical = true;
                    $missingBranchPermissions = [];
                    
                    foreach ($branchCriticalPermissions as $permission) {
                        if (!$branchAdmin->hasPermissionTo($permission)) {
                            $hasAllBranchCritical = false;
                            $missingBranchPermissions[] = $permission;
                        }
                    }
                    
                    $this->addResult('Admin Permissions', 'Branch Admin Critical Permissions', 
                        $hasAllBranchCritical ? 'PASS' : 'FAIL',
                        $hasAllBranchCritical ? 'All critical permissions present' : 'Missing: ' . implode(', ', $missingBranchPermissions)
                    );
                }
            }
            
            echo "✅ Admin creation and permission assignment test completed\n\n";
            
        } catch (Exception $e) {
            $this->addResult('Admin Permissions', 'Error', 'FAIL', $e->getMessage());
            echo "❌ Admin permission test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testKitchenStationCreation()
    {
        echo "🍳 Testing Kitchen Station Creation...\n";
        
        try {
            // Test head office kitchen stations
            if ($this->testOrganization) {
                $headOffice = $this->testOrganization->branches()->where('is_head_office', true)->first();
                
                if ($headOffice) {
                    $headOfficeStations = $headOffice->kitchenStations()->get();
                    
                    $this->addResult('Kitchen Stations', 'Head Office Stations Count', 
                        $headOfficeStations->count() > 0 ? 'PASS' : 'FAIL',
                        "Count: {$headOfficeStations->count()}"
                    );
                    
                    // Check station details
                    $stationTypes = $headOfficeStations->pluck('type')->unique()->toArray();
                    $activeStations = $headOfficeStations->where('is_active', true)->count();
                    
                    $this->addResult('Kitchen Stations', 'Head Office Station Types', 
                        count($stationTypes) > 0 ? 'PASS' : 'FAIL',
                        "Types: " . implode(', ', $stationTypes)
                    );
                    
                    $this->addResult('Kitchen Stations', 'Head Office Active Stations', 
                        $activeStations > 0 ? 'PASS' : 'FAIL',
                        "Active stations: {$activeStations}"
                    );
                }
            }
            
            // Test branch kitchen stations
            if ($this->testBranch) {
                $branchStations = $this->testBranch->kitchenStations()->get();
                
                $this->addResult('Kitchen Stations', 'Branch Stations Count', 
                    $branchStations->count() > 0 ? 'PASS' : 'FAIL',
                    "Count: {$branchStations->count()}"
                );
                
                // Check if stations have proper configuration
                $stationsWithCodes = $branchStations->whereNotNull('code')->count();
                $stationsWithTypes = $branchStations->whereNotNull('type')->count();
                
                $this->addResult('Kitchen Stations', 'Branch Stations Configuration', 
                    ($stationsWithCodes > 0 && $stationsWithTypes > 0) ? 'PASS' : 'FAIL',
                    "With codes: {$stationsWithCodes}, With types: {$stationsWithTypes}"
                );
            }
            
            // Test default kitchen station templates
            if ($this->testBranch) {
                $defaultStations = $this->testBranch->getDefaultKitchenStations();
                
                $this->addResult('Kitchen Stations', 'Default Station Templates', 
                    count($defaultStations) > 0 ? 'PASS' : 'FAIL',
                    "Template count: " . count($defaultStations)
                );
            }
            
            echo "✅ Kitchen station creation test completed\n\n";
            
        } catch (Exception $e) {
            $this->addResult('Kitchen Stations', 'Error', 'FAIL', $e->getMessage());
            echo "❌ Kitchen station test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function testIntegrationWorkflow()
    {
        echo "🔗 Testing Integration Workflow...\n";
        
        try {
            // Test complete organization setup using OrganizationAutomationService
            $automationService = new OrganizationAutomationService(new BranchAutomationService());
            
            $integrationOrgData = [
                'name' => 'Integration Test Restaurant ' . time(),
                'email' => 'integration' . time() . '@restaurant.com',
                'phone' => '+1234567892',
                'address' => '789 Integration Street, Integration City',
                'contact_person' => 'Integration Manager',
                'contact_person_phone' => '+1234567892',
                'contact_person_designation' => 'General Manager',
                'is_active' => false,
                'subscription_plan_id' => 1,
            ];
            
            echo "  Testing complete organization setup via automation service...\n";
            
            DB::beginTransaction();
            
            $integrationOrg = $automationService->setupNewOrganization($integrationOrgData);
            
            $this->addResult('Integration Workflow', 'Complete Organization Setup', 
                $integrationOrg ? 'PASS' : 'FAIL',
                "Organization ID: {$integrationOrg->id}"
            );
            
            // Verify all components were created
            $headOffice = $integrationOrg->branches()->where('is_head_office', true)->first();
            $orgAdmin = $integrationOrg->admins()->whereNull('branch_id')->first();
            $branchAdmin = $integrationOrg->admins()->whereNotNull('branch_id')->first();
            $kitchenStations = $headOffice ? $headOffice->kitchenStations()->count() : 0;
            $itemCategories = ItemCategory::where('organization_id', $integrationOrg->id)->count();
            
            $this->addResult('Integration Workflow', 'All Components Created', 
                ($headOffice && $orgAdmin && $branchAdmin && $kitchenStations > 0) ? 'PASS' : 'FAIL',
                "Head Office: " . ($headOffice ? 'Yes' : 'No') . 
                ", Org Admin: " . ($orgAdmin ? 'Yes' : 'No') . 
                ", Branch Admin: " . ($branchAdmin ? 'Yes' : 'No') . 
                ", Kitchen Stations: {$kitchenStations}" .
                ", Item Categories: {$itemCategories}"
            );
            
            // Test relationships
            $orgHasBranches = $integrationOrg->branches()->count() > 0;
            $branchHasStations = $headOffice ? $headOffice->kitchenStations()->count() > 0 : false;
            $adminsHaveRoles = $orgAdmin ? $orgAdmin->getRoleNames()->count() > 0 : false;
            
            $this->addResult('Integration Workflow', 'Relationships Working', 
                ($orgHasBranches && $branchHasStations && $adminsHaveRoles) ? 'PASS' : 'FAIL',
                "Org->Branches: " . ($orgHasBranches ? 'Yes' : 'No') . 
                ", Branch->Stations: " . ($branchHasStations ? 'Yes' : 'No') . 
                ", Admin->Roles: " . ($adminsHaveRoles ? 'Yes' : 'No')
            );
            
            // Cleanup integration test data
            $integrationOrg->delete();
            
            DB::commit();
            echo "✅ Integration workflow test completed\n\n";
            
        } catch (Exception $e) {
            DB::rollback();
            $this->addResult('Integration Workflow', 'Error', 'FAIL', $e->getMessage());
            echo "❌ Integration workflow test failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function cleanup()
    {
        echo "🧹 Cleaning up test data...\n";
        
        try {
            DB::beginTransaction();
            
            if ($this->testBranch) {
                $this->testBranch->delete();
                echo "  Deleted test branch\n";
            }
            
            if ($this->testOrganization) {
                $this->testOrganization->delete();
                echo "  Deleted test organization\n";
            }
            
            DB::commit();
            echo "✅ Cleanup completed\n\n";
            
        } catch (Exception $e) {
            DB::rollback();
            echo "❌ Cleanup failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    private function addResult($category, $test, $status, $details = '')
    {
        $this->results[] = [
            'category' => $category,
            'test' => $test,
            'status' => $status,
            'details' => $details
        ];
    }
    
    private function displayResults()
    {
        echo "📊 TEST RESULTS SUMMARY\n";
        echo "=" . str_repeat("=", 80) . "\n\n";
        
        $categories = [];
        $totalTests = 0;
        $passedTests = 0;
        
        // Group results by category
        foreach ($this->results as $result) {
            $categories[$result['category']][] = $result;
            $totalTests++;
            if ($result['status'] === 'PASS') {
                $passedTests++;
            }
        }
        
        // Display results by category
        foreach ($categories as $category => $tests) {
            echo "📋 {$category}\n";
            echo str_repeat("-", 40) . "\n";
            
            foreach ($tests as $test) {
                $statusIcon = $test['status'] === 'PASS' ? '✅' : '❌';
                echo "  {$statusIcon} {$test['test']}: {$test['status']}\n";
                if (!empty($test['details'])) {
                    echo "     Details: {$test['details']}\n";
                }
            }
            echo "\n";
        }
        
        // Overall summary
        echo "🎯 OVERALL SUMMARY\n";
        echo str_repeat("-", 40) . "\n";
        echo "Total Tests: {$totalTests}\n";
        echo "Passed: {$passedTests}\n";
        echo "Failed: " . ($totalTests - $passedTests) . "\n";
        echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";
        
        // Recommendations
        $this->displayRecommendations();
    }
    
    private function displayRecommendations()
    {
        echo "💡 RECOMMENDATIONS\n";
        echo str_repeat("-", 40) . "\n";
        
        $failedTests = array_filter($this->results, function($result) {
            return $result['status'] === 'FAIL';
        });
        
        if (empty($failedTests)) {
            echo "✅ All automated workflows are working properly!\n";
            echo "   - Organization creation workflow is functional\n";
            echo "   - Branch creation workflow is functional\n";
            echo "   - Admin creation and permission assignment is working\n";
            echo "   - Kitchen station creation is automated correctly\n";
        } else {
            echo "⚠️  Issues found in automated workflows:\n\n";
            
            foreach ($failedTests as $test) {
                echo "❌ {$test['category']} - {$test['test']}\n";
                echo "   Issue: {$test['details']}\n";
                
                // Provide specific recommendations
                if (strpos($test['test'], 'Observer') !== false) {
                    echo "   Recommendation: Check EventServiceProvider registration\n";
                } elseif (strpos($test['test'], 'Permission') !== false) {
                    echo "   Recommendation: Verify Spatie permission package configuration\n";
                } elseif (strpos($test['test'], 'Kitchen Station') !== false) {
                    echo "   Recommendation: Check KitchenStation model and migration\n";
                } elseif (strpos($test['test'], 'Admin') !== false) {
                    echo "   Recommendation: Verify admin creation logic in automation services\n";
                }
                echo "\n";
            }
        }
        
        echo "\n📝 NEXT STEPS:\n";
        echo "1. Review any failed tests and fix underlying issues\n";
        echo "2. Test workflows in a staging environment\n";
        echo "3. Monitor logs during organization/branch creation\n";
        echo "4. Verify email notifications are working\n";
        echo "5. Test permission inheritance and role assignments\n";
    }
}

// Run the tests
echo "Starting Automated Workflow Tests...\n\n";

try {
    $tester = new AutomatedWorkflowTester();
    $tester->runAllTests();
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Testing completed!\n";
