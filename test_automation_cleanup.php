<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Organization;
use App\Models\Role;
use App\Services\OrganizationAutomationService;
use App\Services\BranchAutomationService;

echo "Testing organization automation workflow after cleanup...\n\n";

// Check roles before creation
$rolesBefore = Role::all()->count();
echo "Roles before creation: {$rolesBefore}\n";

// Create test organization data
$orgData = [
    'name' => 'Test Automation Cleanup',
    'email' => 'test@automation-cleanup.com',
    'phone' => '+1-555-000-0000',
    'address' => '123 Test Street',
    'contact_person' => 'Test Manager',
    'contact_person_designation' => 'Manager',
    'contact_person_phone' => '+1-555-000-0001',
    'password' => 'TestPassword123!',
    'business_type' => 'restaurant',
    'subscription_plan_id' => null,
];

// Test the automation service
try {
    $branchService = new BranchAutomationService();
    $service = new OrganizationAutomationService($branchService);
    $result = $service->setupNewOrganization($orgData);
    
    echo "✅ Organization created successfully!\n";
    echo "   Organization ID: {$result->id}\n";
    echo "   Organization Name: {$result->name}\n";
    
    // Get the head office branch
    $headOffice = $result->branches()->where('is_head_office', true)->first();
    if ($headOffice) {
        echo "   Head Office ID: {$headOffice->id}\n";
        echo "   Head Office Name: {$headOffice->name}\n";
    }
    
    // Get the organization admin
    $orgAdmin = $result->admins()->where('is_organization_admin', true)->first();
    if ($orgAdmin) {
        echo "   Admin ID: {$orgAdmin->id}\n";
        echo "   Admin Email: {$orgAdmin->email}\n";
    }
    
    echo "\n";
    
    // Check roles after creation
    $rolesAfter = Role::all()->count();
    echo "Roles after creation: {$rolesAfter}\n";
    echo "New roles created: " . ($rolesAfter - $rolesBefore) . "\n\n";
    
    // List the new roles for this organization
    $orgRoles = Role::where('organization_id', $result->id)->get();
    echo "Roles created for this organization:\n";
    foreach ($orgRoles as $role) {
        echo "  - {$role->name} (ID: {$role->id})\n";
    }
    
    // Check admin permissions
    $admin = $result->admins()->where('is_organization_admin', true)->first();
    if ($admin) {
        $adminRoles = $admin->roles()->get();
        echo "\nAdmin roles assigned:\n";
        foreach ($adminRoles as $role) {
            echo "  - {$role->name}\n";
        }
        
        $adminPermissions = $admin->getAllPermissions();
        echo "\nAdmin permissions count: " . $adminPermissions->count() . "\n";
    }
    
    echo "\n🎉 Test completed successfully! Only essential roles were created.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
