<?php
/*
 * Simple test to verify super admin dashboard access
 * Run with: php artisan tinker
 */

// Test the super admin
$admin = \App\Models\Admin::where('email', 'superadmin@rms.com')->first();
if ($admin) {
    echo "Super admin found: {$admin->email}\n";
    echo "Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
    echo "is_super_admin: " . ($admin->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "Has Super Admin Role: " . ($admin->hasRole('Super Admin') ? 'YES' : 'NO') . "\n";
    
    // Test the dashboard logic
    $isSuperAdmin = $admin->is_super_admin || $admin->hasRole('Super Admin');
    echo "Dashboard access check: " . ($isSuperAdmin ? 'ALLOWED' : 'BLOCKED') . "\n";
    
    // Check the specific condition from AdminController
    if (!$isSuperAdmin && !$admin->organization_id) {
        echo "Would be redirected: YES (missing org and not super admin)\n";
    } else {
        echo "Would be redirected: NO (super admin or has org)\n";
    }
} else {
    echo "Super admin not found!\n";
}
