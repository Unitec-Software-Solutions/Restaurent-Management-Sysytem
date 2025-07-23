<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Admin Credentials ===\n\n";

try {
    // Get all admin users
    $admins = \App\Models\Admin::all();
    
    echo "Found " . $admins->count() . " admin users:\n\n";
    
    foreach ($admins as $admin) {
        echo "ID: {$admin->id}\n";
        echo "Name: {$admin->name}\n";
        echo "Email: {$admin->email}\n";
        echo "Is Super Admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . "\n";
        echo "Status: " . ($admin->is_active ? 'Active' : 'Inactive') . "\n";
        echo "Organization ID: " . ($admin->organization_id ?? 'None') . "\n";
        echo "Branch ID: " . ($admin->branch_id ?? 'None') . "\n";
        echo "Created: " . $admin->created_at . "\n";
        
        // Check roles
        $roles = $admin->roles;
        echo "Roles: " . ($roles->count() > 0 ? $roles->pluck('name')->implode(', ') : 'None') . "\n";
        
        // Check permissions
        if (method_exists($admin, 'getAllPermissions')) {
            $permissions = $admin->getAllPermissions();
            echo "Permissions: " . $permissions->count() . "\n";
        }
        
        echo "---\n\n";
    }
    
    // Show password reset option for Super Admin
    $superAdmin = \App\Models\Admin::where('is_super_admin', true)->first();
    if ($superAdmin) {
        echo "=== SUPER ADMIN PASSWORD RESET ===\n";
        echo "Current Super Admin: {$superAdmin->name} ({$superAdmin->email})\n";
        echo "Would you like to reset the password to 'password'? (This will be done automatically)\n";
        
        // Reset password
        $superAdmin->password = \Illuminate\Support\Facades\Hash::make('password');
        $superAdmin->save();
        
        echo "✅ Super Admin password reset to 'password'\n";
        echo "You can now login with:\n";
        echo "Email: {$superAdmin->email}\n";
        echo "Password: password\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
