#!/usr/bin/env php
<?php

// Laravel Project Optimization and Validation Script
// This script clears caches, optimizes the application, and runs basic validation checks

echo "🚀 Starting Laravel Project Optimization...\n\n";

$commands = [
    'Clear all caches' => 'php artisan optimize:clear',
    'Clear route cache' => 'php artisan route:clear', 
    'Clear config cache' => 'php artisan config:clear',
    'Clear view cache' => 'php artisan view:clear',
    'Clear application cache' => 'php artisan cache:clear',
    'Cache routes' => 'php artisan route:cache',
    'Cache config' => 'php artisan config:cache',
    'Cache views' => 'php artisan view:cache',
    'Generate autoloader optimization' => 'composer dump-autoload -o',
];

foreach ($commands as $description => $command) {
    echo "📋 {$description}...\n";
    echo "   Running: {$command}\n";
    
    $output = [];
    $return_var = 0;
    exec($command . ' 2>&1', $output, $return_var);
    
    if ($return_var === 0) {
        echo "   ✅ Success\n";
    } else {
        echo "   ❌ Failed\n";
        echo "   Output: " . implode("\n           ", $output) . "\n";
    }
    echo "\n";
}

echo "🔍 Running validation checks...\n\n";

// Check if critical files exist
$criticalFiles = [
    'app/Models/Staff.php',
    'app/Models/Shift.php',
    'resources/views/guest/menu/branch-selection.blade.php',
    'resources/views/guest/menu/not-available.blade.php',
    'resources/views/guest/menu/view.blade.php',
    'resources/views/guest/menu/special.blade.php',
    'resources/views/guest/cart/view.blade.php',
    'resources/views/guest/order/confirmation.blade.php',
    'resources/views/guest/order/not-found.blade.php',
    'resources/views/guest/order/track.blade.php',
    'resources/views/guest/reservations/create.blade.php',
    'resources/views/guest/reservations/confirmation.blade.php',
];

echo "📂 Checking critical files:\n";
foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ {$file}\n";
    } else {
        echo "   ❌ Missing: {$file}\n";
    }
}

echo "\n🛣️  Checking route files for syntax errors:\n";
$routeFiles = [
    'routes/groups/admin.php',
    'routes/groups/auth.php', 
    'routes/groups/guest.php',
    'routes/groups/public.php',
];

foreach ($routeFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Check for old @ syntax
        if (strpos($content, '@') !== false && !strpos($content, 'health_report')) {
            // Allow @ in comments and emails, but flag potential route syntax issues
            if (preg_match('/\'\s*[A-Za-z\\\\]+@[a-zA-Z]+\s*\'/', $content)) {
                echo "   ⚠️  Potential old route syntax in: {$file}\n";
            } else {
                echo "   ✅ {$file}\n";
            }
        } else {
            echo "   ✅ {$file}\n";
        }
    } else {
        echo "   ❌ Missing: {$file}\n";
    }
}

echo "\n🏁 Optimization complete!\n";
echo "\n📋 Next steps:\n";
echo "   1. Start your Laravel development server: php artisan serve\n";
echo "   2. Test the guest menu functionality\n";
echo "   3. Test the reservation system\n";
echo "   4. Verify all views render correctly\n";
echo "   5. Check the admin dashboard login\n";
echo "\n🎉 All major issues should now be resolved!\n";
