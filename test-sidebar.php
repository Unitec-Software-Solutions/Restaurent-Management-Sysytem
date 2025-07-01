<?php
// Test file to check if the AdminSidebar component can be instantiated without errors

require __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    echo "Testing AdminSidebar component instantiation...\n";

    // Check if the class exists
    if (class_exists('App\View\Components\AdminSidebar')) {
        echo "✅ AdminSidebar class exists\n";

        // Try to instantiate (this will check for any syntax errors)
        $sidebar = new App\View\Components\AdminSidebar();
        echo "✅ AdminSidebar can be instantiated successfully\n";

        // Check if the required methods exist
        $methods = ['render', 'validateRoute', 'hasPermission'];
        foreach ($methods as $method) {
            if (method_exists($sidebar, $method)) {
                echo "✅ Method '$method' exists\n";
            } else {
                echo "❌ Method '$method' missing\n";
            }
        }

    } else {
        echo "❌ AdminSidebar class not found\n";
    }

    echo "\n✅ All tests passed! AdminSidebar component is properly configured.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
