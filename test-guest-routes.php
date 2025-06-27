<?php

// Quick route test to check guest routes
echo "🔍 Testing Guest Routes...\n\n";

// Test route names
$routes = [
    'guest.menu.branch-selection',
    'guest.menu.view',
    'guest.menu.date',
    'guest.menu.special',
    'guest.cart.view',
    'guest.cart.add',
    'guest.order.create',
    'guest.order.confirmation',
    'guest.order.track',
    'guest.reservations.create',
    'guest.reservations.store',
    'guest.reservations.confirmation',
];

foreach ($routes as $routeName) {
    try {
        $url = route($routeName);
        echo "✅ {$routeName} -> {$url}\n";
    } catch (Exception $e) {
        echo "❌ {$routeName} -> ERROR: {$e->getMessage()}\n";
    }
}

echo "\n🔧 Additional Tests:\n";

// Test with parameters
try {
    $url = route('guest.menu.view', ['branchId' => 1]);
    echo "✅ guest.menu.view with branchId -> {$url}\n";
} catch (Exception $e) {
    echo "❌ guest.menu.view with branchId -> ERROR: {$e->getMessage()}\n";
}

try {
    $url = route('guest.menu.special', ['branchId' => 1]);
    echo "✅ guest.menu.special with branchId -> {$url}\n";
} catch (Exception $e) {
    echo "❌ guest.menu.special with branchId -> ERROR: {$e->getMessage()}\n";
}
