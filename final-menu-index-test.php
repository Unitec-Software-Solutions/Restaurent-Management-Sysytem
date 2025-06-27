<?php

echo "=== Final Menu Index Route Fix Test ===\n\n";

// Test 1: Verify all routes are properly defined
echo "1. Testing route availability via artisan:\n";
$command = 'php artisan route:list --name=admin.menus 2>&1';
$output = shell_exec($command);

if (strpos($output, 'admin.menus.bulk-create') !== false) {
    echo "✓ admin.menus.bulk-create route exists\n";
} else {
    echo "✗ admin.menus.bulk-create route missing\n";
}

if (strpos($output, 'admin.menus.create') !== false) {
    echo "✓ admin.menus.create route exists\n";
} else {
    echo "✗ admin.menus.create route missing\n";
}

if (strpos($output, 'admin.menus.index') !== false) {
    echo "✓ admin.menus.index route exists\n";
} else {
    echo "✗ admin.menus.index route missing\n";
}

// Test 2: Check view file syntax
echo "\n2. Checking view file syntax:\n";
$viewFile = 'resources/views/admin/menus/index.blade.php';

if (file_exists($viewFile)) {
    $viewContent = file_get_contents($viewFile);
    
    // Check for balanced Blade syntax
    $openIf = substr_count($viewContent, '@if');
    $closeIf = substr_count($viewContent, '@endif');
    
    if ($openIf === $closeIf) {
        echo "✓ Blade @if/@endif statements are balanced ($openIf each)\n";
    } else {
        echo "✗ Blade @if/@endif statements are unbalanced ($openIf @if, $closeIf @endif)\n";
    }
    
    // Check for route syntax errors
    $routePattern = '/route\([\'"]([^\'"]*)[\'"].*?\)/';
    preg_match_all($routePattern, $viewContent, $matches);
    
    echo "✓ Found " . count($matches[1]) . " route() calls in view\n";
    
    $invalidRoutes = [];
    foreach ($matches[1] as $route) {
        // Check for common route naming issues
        if (strpos($route, '.bulk.') !== false) {
            $invalidRoutes[] = $route;
        }
    }
    
    if (empty($invalidRoutes)) {
        echo "✓ No invalid route names found\n";
    } else {
        echo "✗ Invalid route names found: " . implode(', ', $invalidRoutes) . "\n";
    }
    
} else {
    echo "✗ View file not found: $viewFile\n";
}

// Test 3: Check controller method
echo "\n3. Checking controller method:\n";
$controllerFile = 'app/Http/Controllers/Admin/MenuController.php';

if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    if (preg_match('/public function bulkCreate\(\).*?:\s*View/', $controllerContent)) {
        echo "✓ bulkCreate method exists with proper return type\n";
    } else {
        echo "✗ bulkCreate method missing or improperly defined\n";
    }
    
    if (strpos($controllerContent, "return view('admin.menus.bulk-create'") !== false) {
        echo "✓ bulkCreate method returns correct view\n";
    } else {
        echo "✗ bulkCreate method doesn't return correct view\n";
    }
    
} else {
    echo "✗ Controller file not found: $controllerFile\n";
}

// Test 4: Check bulk-create view exists
echo "\n4. Checking bulk-create view:\n";
$bulkCreateView = 'resources/views/admin/menus/bulk-create.blade.php';

if (file_exists($bulkCreateView)) {
    echo "✓ bulk-create.blade.php view exists\n";
    
    $bulkViewContent = file_get_contents($bulkCreateView);
    if (strpos($bulkViewContent, '@extends') !== false) {
        echo "✓ bulk-create view extends a layout\n";
    } else {
        echo "⚠ bulk-create view doesn't extend a layout\n";
    }
    
} else {
    echo "✗ bulk-create.blade.php view not found\n";
}

// Test 5: Summary
echo "\n5. Test Summary:\n";
echo "The route [admin.menus.bulk.create] not defined error should now be resolved.\n";
echo "The issue was caused by using dots instead of hyphens in the route name.\n";
echo "Fixed: admin.menus.bulk.create → admin.menus.bulk-create\n";

echo "\n=== Test Complete ===\n";
