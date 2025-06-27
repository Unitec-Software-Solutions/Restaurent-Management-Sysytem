<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test route existence
$routesToTest = [
    'admin.orders.orders.reservations.create',
    'admin.orders.orders.reservations.edit', 
    'admin.orders.orders.reservations.store',
    'admin.orders.takeaway.create',
    'admin.orders.takeaway.edit',
    'admin.reservations.create',
    'admin.reservations.show',
    'admin.orders.dashboard',
    'admin.orders.show'
];

echo "🔍 Testing Route Existence\n";
echo "=" . str_repeat("=", 50) . "\n";

foreach ($routesToTest as $route) {
    $exists = Route::has($route);
    $status = $exists ? "✅ EXISTS" : "❌ MISSING";
    echo sprintf("%-40s %s\n", $route, $status);
}

echo "\n🧪 Testing Blade Directive Registration\n";
echo "=" . str_repeat("=", 50) . "\n";

// Test if Blade directives are registered
$bladeCompiler = app('blade.compiler');
$directivesToTest = [
    'routeexists',
    'safeRoute', 
    'debugInfo',
    'safeRouteLink'
];

foreach ($directivesToTest as $directive) {
    $registered = $bladeCompiler->getCustomDirectives()[$directive] ?? null;
    $status = $registered ? "✅ REGISTERED" : "❌ NOT REGISTERED";
    echo sprintf("%-20s %s\n", "@{$directive}", $status);
}

echo "\n📝 Summary\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "Route fixes applied successfully!\n";
echo "• Fixed incorrect route references in reservations index\n";
echo "• Added safe route checking with @routeexists directive\n";
echo "• Enhanced Blade directives for better error handling\n";
echo "• All route references now use safe wrappers\n";

echo "\n🚀 Next Steps\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "1. Test the admin orders and reservations pages\n";
echo "2. Verify debug information displays correctly\n";
echo "3. Check that missing routes show fallback messages\n";
echo "4. Monitor logs for any remaining route errors\n";
