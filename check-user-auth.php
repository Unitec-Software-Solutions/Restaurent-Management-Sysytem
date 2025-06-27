<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';

// Boot the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 CHECKING USER AUTHENTICATION ISSUE\n";
echo "=====================================\n\n";

try {
    // Check if users table exists
    $userCount = \App\Models\User::count();
    echo "📊 Total users in database: {$userCount}\n\n";

    if ($userCount > 0) {
        echo "👥 Users found:\n";
        $users = \App\Models\User::select('id', 'email', 'name', 'is_active', 'is_super_admin', 'password')->get();
        
        foreach ($users as $user) {
            echo "  ID: {$user->id}\n";
            echo "  Email: {$user->email}\n";
            echo "  Name: {$user->name}\n";
            echo "  Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
            echo "  Super Admin: " . ($user->is_super_admin ? 'Yes' : 'No') . "\n";
            echo "  Password Hash: " . substr($user->password, 0, 20) . "...\n";
            echo "  ---\n";
        }
        
        // Check specific super admin
        $superAdmin = \App\Models\User::where('email', 'superadmin@rms.com')->first();
        if ($superAdmin) {
            echo "\n🔑 Super Admin Found:\n";
            echo "  Email: {$superAdmin->email}\n";
            echo "  Active: " . ($superAdmin->is_active ? 'Yes' : 'No') . "\n";
            echo "  Password verification test: ";
            if (\Illuminate\Support\Facades\Hash::check('password', $superAdmin->password)) {
                echo "✅ PASSWORD MATCHES\n";
            } else {
                echo "❌ PASSWORD DOES NOT MATCH\n";
            }
        } else {
            echo "\n❌ Super Admin (superadmin@rms.com) NOT FOUND\n";
        }
    } else {
        echo "❌ No users found in database\n";
    }

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
