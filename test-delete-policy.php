<?php

require_once 'vendor/autoload.php';

use App\Models\Admin;
use App\Models\Organization;
use App\Policies\OrganizationPolicy;

$app = require_once 'bootstrap/app.php';

// Boot the application
$app->boot();

echo "Testing Organization Deletion Policy...\n";

try {
    $admin = Admin::where('email', 'superadmin@rms.com')->first();
    $org = Organization::where('is_active', false)->first();
    
    if (!$admin) {
        echo "❌ No super admin found\n";
        exit(1);
    }
    
    if (!$org) {
        echo "❌ No inactive organization found\n";
        exit(1);
    }
    
    echo "✅ Admin found: {$admin->name} (ID: {$admin->id})\n";
    echo "✅ Organization found: {$org->name} (ID: {$org->id})\n";
    echo "📊 Admin is super admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . "\n";
    echo "📊 Organization is inactive: " . (!$org->is_active ? 'Yes' : 'No') . "\n";
    
    // Test the policy
    $policy = new OrganizationPolicy();
    $canDelete = $policy->delete($admin, $org);
    
    echo "🔒 Policy allows deletion: " . ($canDelete ? 'Yes' : 'No') . "\n";
    
    // Test if organization has branches/users
    $branchCount = $org->branches()->count();
    $userCount = $org->users()->count();
    
    echo "📈 Branch count: {$branchCount}\n";
    echo "📈 User count: {$userCount}\n";
    
    if ($canDelete && $branchCount == 0 && $userCount == 0) {
        echo "✅ Organization meets all criteria for deletion\n";
    } else {
        echo "❌ Organization cannot be deleted:\n";
        if (!$canDelete) echo "  - Policy denies deletion\n";
        if ($branchCount > 0) echo "  - Has {$branchCount} branches\n";
        if ($userCount > 0) echo "  - Has {$userCount} users\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
