<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';

// Boot the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 CHECKING ADMIN AUTHENTICATION\n";
echo "================================\n\n";

try {
    // Check admins table
    $adminCount = \App\Models\Admin::count();
    echo "📊 Total admins in database: {$adminCount}\n\n";

    if ($adminCount > 0) {
        echo "👥 Admins found:\n";
        $admins = \App\Models\Admin::all();
        
        foreach ($admins as $admin) {
            echo "  Email: {$admin->email}\n";
            echo "  Name: {$admin->name}\n";
            echo "  Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
            echo "  Super Admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . "\n";
            echo "  Password Hash: " . substr($admin->password, 0, 20) . "...\n";
            
            // Test password
            echo "  Password Test: ";
            if (\Illuminate\Support\Facades\Hash::check('password', $admin->password)) {
                echo "✅ 'password' matches\n";
            } elseif (\Illuminate\Support\Facades\Hash::check('password123', $admin->password)) {
                echo "✅ 'password123' matches\n";
            } else {
                echo "❌ Neither 'password' nor 'password123' match\n";
            }
            echo "  ---\n";
        }
    } else {
        echo "❌ No admins found in database\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
