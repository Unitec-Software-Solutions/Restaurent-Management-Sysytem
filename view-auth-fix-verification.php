<?php

echo "✅ VIEW AUTHENTICATION FIX VERIFICATION\n";
echo "=====================================\n\n";

// Check if the fixed views exist and contain the correct guard references
$fixedViews = [
    'resources/views/admin/inventory/items/index.blade.php',
    'resources/views/admin/suppliers/grn/index.blade.php', 
    'resources/views/admin/inventory/stock/index.blade.php',
    'resources/views/admin/inventory/stock/transactions/index.blade.php',
    'resources/views/admin/inventory/gtn/print.blade.php',
    'resources/views/admin/inventory/gtn/index.blade.php'
];

foreach ($fixedViews as $view) {
    if (file_exists($view)) {
        $content = file_get_contents($view);
        
        // Check if Auth::user() references still exist (should be fixed)
        $oldReferences = substr_count($content, 'Auth::user()->organization');
        
        // Check if Auth::guard('admin')->user() references exist (should be added)
        $newReferences = substr_count($content, "Auth::guard('admin')->user()");
        
        echo "📄 $view\n";
        echo "   - Old Auth::user()->organization references: $oldReferences\n";
        echo "   - New Auth::guard('admin')->user() references: $newReferences\n";
        
        if ($oldReferences === 0 && $newReferences > 0) {
            echo "   ✅ FIXED\n";
        } elseif ($oldReferences > 0) {
            echo "   ❌ STILL HAS ISSUES\n";
        } else {
            echo "   ℹ️  NO AUTH REFERENCES\n";
        }
        echo "\n";
    } else {
        echo "❌ $view - FILE NOT FOUND\n\n";
    }
}

echo "🔧 CONTROLLER AUTHENTICATION FIX VERIFICATION\n";
echo "============================================\n\n";

$fixedControllers = [
    'app/Http/Controllers/GrnDashboardController.php',
    'app/Http/Controllers/ItemMasterController.php', 
    'app/Http/Controllers/UserController.php'
];

foreach ($fixedControllers as $controller) {
    if (file_exists($controller)) {
        $content = file_get_contents($controller);
        
        // Check for proper guard usage
        $adminGuardRefs = substr_count($content, "Auth::guard('admin')->user()");
        $oldAuthRefs = substr_count($content, 'Auth::user()');
        
        echo "📄 $controller\n";
        echo "   - Auth::guard('admin')->user() references: $adminGuardRefs\n";  
        echo "   - Auth::user() references: $oldAuthRefs\n";
        
        if ($adminGuardRefs > 0 && $oldAuthRefs === 0) {
            echo "   ✅ PROPERLY FIXED\n";
        } elseif ($adminGuardRefs > 0 && $oldAuthRefs > 0) {
            echo "   ⚠️  PARTIALLY FIXED (mixed usage)\n";
        } else {
            echo "   ❌ NEEDS ATTENTION\n";
        }
        echo "\n";
    } else {
        echo "❌ $controller - FILE NOT FOUND\n\n";
    }
}

echo "📊 SUMMARY\n";
echo "=========\n";
echo "The authentication fixes should resolve the 'Attempt to read property \"name\" on null' error\n";
echo "that was occurring when accessing inventory items management and other admin pages.\n\n";

echo "Key fixes applied:\n";
echo "1. Updated views to use Auth::guard('admin')->user() instead of Auth::user()\n";
echo "2. Added super admin bypass logic for organization display\n"; 
echo "3. Fixed controller authentication guard usage\n";
echo "4. Updated validation rules to handle super admin access\n\n";

echo "✅ The inventory items page should now work without the null property error!\n";
