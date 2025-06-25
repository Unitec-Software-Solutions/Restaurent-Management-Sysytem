<?php

// Test isSuperAdmin implementation
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing isSuperAdmin() method implementation...\n\n";

try {
    $admin = App\Models\Admin::first();
    
    if (!$admin) {
        echo "❌ No admin found in database\n";
        exit(1);
    }
    
    echo "✅ Found admin: {$admin->name} ({$admin->email})\n";
    echo "   - is_super_admin column: " . ($admin->is_super_admin ? 'true' : 'false') . "\n";
    echo "   - role column: " . ($admin->role ?? 'null') . "\n";
    
    // Test the method
    $isSuperAdmin = $admin->isSuperAdmin();
    echo "   - isSuperAdmin() method: " . ($isSuperAdmin ? 'true' : 'false') . "\n";
    
    // Test other methods
    echo "   - hasOrganizationAccess(): " . ($admin->hasOrganizationAccess() ? 'true' : 'false') . "\n";
    echo "   - canManageAdmins(): " . ($admin->canManageAdmins() ? 'true' : 'false') . "\n";
    echo "   - canManageSystem(): " . ($admin->canManageSystem() ? 'true' : 'false') . "\n";
    
    echo "\n✅ isSuperAdmin() method working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error testing isSuperAdmin method: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
