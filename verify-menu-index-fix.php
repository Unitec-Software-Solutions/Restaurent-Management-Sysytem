<?php

echo "=== Verifying Menu Index Route Fix ===\n\n";

// Check if the view file exists and contains correct route references
$viewFile = __DIR__ . '/resources/views/admin/menus/index.blade.php';

if (!file_exists($viewFile)) {
    echo "✗ View file not found: $viewFile\n";
    exit(1);
}

$viewContent = file_get_contents($viewFile);

echo "1. Checking for problematic route references:\n";

// Check for the old incorrect route name
if (strpos($viewContent, 'admin.menus.bulk.create') !== false) {
    echo "✗ Found incorrect route name 'admin.menus.bulk.create'\n";
} else {
    echo "✓ No incorrect route name 'admin.menus.bulk.create' found\n";
}

// Check for correct route references
$correctRoutes = [
    'admin.menus.bulk-create',
    'admin.menus.create',
    'admin.menus.index',
    'admin.menus.list',
    'admin.menus.calendar'
];

echo "\n2. Checking for correct route references:\n";
foreach ($correctRoutes as $route) {
    if (strpos($viewContent, $route) !== false) {
        echo "✓ Found correct route: $route\n";
    } else {
        echo "? Route not found in view: $route\n";
    }
}

// Check routes file
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    echo "\n3. Checking route definitions in web.php:\n";
    
    // Check if bulk-create route is defined
    if (strpos($routesContent, 'admin.menus.bulk-create') !== false) {
        echo "✓ Route 'admin.menus.bulk-create' is defined in web.php\n";
    } else {
        echo "✗ Route 'admin.menus.bulk-create' not found in web.php\n";
    }
    
    // Check if bulkCreate method is referenced
    if (strpos($routesContent, 'bulkCreate') !== false) {
        echo "✓ bulkCreate method is referenced in web.php\n";
    } else {
        echo "✗ bulkCreate method not found in web.php\n";
    }
}

// Check if MenuController has bulkCreate method
$controllerFile = __DIR__ . '/app/Http/Controllers/Admin/MenuController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    echo "\n4. Checking MenuController for required methods:\n";
    
    if (strpos($controllerContent, 'public function bulkCreate') !== false) {
        echo "✓ bulkCreate method exists in MenuController\n";
    } else {
        echo "✗ bulkCreate method not found in MenuController\n";
    }
    
    if (strpos($controllerContent, 'public function bulkStore') !== false) {
        echo "✓ bulkStore method exists in MenuController\n";
    } else {
        echo "✗ bulkStore method not found in MenuController\n";
    }
}

echo "\n5. Analyzing view structure:\n";

// Check for common view issues
if (strpos($viewContent, '@extends(\'layouts.admin\')') !== false) {
    echo "✓ View extends admin layout\n";
} else {
    echo "✗ View doesn't extend admin layout properly\n";
}

// Check for malformed HTML/Blade syntax
$openTags = substr_count($viewContent, '<a href=');
$closeTags = substr_count($viewContent, '</a>');

if ($openTags === $closeTags) {
    echo "✓ HTML anchor tags are balanced ($openTags opening, $closeTags closing)\n";
} else {
    echo "⚠ HTML anchor tags may be unbalanced ($openTags opening, $closeTags closing)\n";
}

// Check for route helper usage
$routeCalls = preg_match_all('/route\([\'"]([^\'"]+)[\'"]\)/', $viewContent, $matches);
if ($routeCalls > 0) {
    echo "✓ Found $routeCalls route() calls in view\n";
    echo "  Routes referenced: " . implode(', ', array_unique($matches[1])) . "\n";
} else {
    echo "✗ No route() calls found in view\n";
}

echo "\n=== Route Fix Verification Complete ===\n";
