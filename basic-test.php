<?php
echo "Testing basic PHP execution...\n";

try {
    require_once 'vendor/autoload.php';
    echo "✅ Autoload successful\n";
    
    $app = require_once 'bootstrap/app.php';
    echo "✅ Laravel app loaded\n";
    
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "✅ Laravel bootstrapped\n";
    
    echo "✅ Basic test complete\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
