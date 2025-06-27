<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

echo "🔍 CHECKING ADMIN ACCOUNTS IN DATABASE\n";
echo "=====================================\n";

$admins = App\Models\Admin::all();

if ($admins->isEmpty()) {
    echo "❌ NO ADMIN ACCOUNTS FOUND!\n";
} else {
    echo "📊 Found " . $admins->count() . " admin account(s):\n\n";
    foreach ($admins as $admin) {
        echo "  👤 {$admin->name}\n";
        echo "     📧 Email: {$admin->email}\n";
        echo "     🔐 Super Admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . "\n";
        echo "     🏢 Organization: " . ($admin->organization ? $admin->organization->name : 'None') . "\n";
        echo "     🌿 Branch: " . ($admin->branch ? $admin->branch->name : 'None') . "\n";
        echo "     ✅ Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";
        echo "\n";
    }
}

echo "🔍 CHECKING USER ACCOUNTS IN DATABASE\n";
echo "=====================================\n";

$users = App\Models\User::all();

if ($users->isEmpty()) {
    echo "❌ NO USER ACCOUNTS FOUND!\n";
} else {
    echo "📊 Found " . $users->count() . " user account(s):\n\n";
    foreach ($users as $user) {
        echo "  👤 {$user->name}\n";
        echo "     📧 Email: {$user->email}\n";
        echo "     🔐 Super Admin: " . ($user->is_super_admin ?? false ? 'Yes' : 'No') . "\n";
        echo "     🏢 Organization: " . ($user->organization ? $user->organization->name : 'None') . "\n";
        echo "     🌿 Branch: " . ($user->branch ? $user->branch->name : 'None') . "\n";
        echo "\n";
    }
}
