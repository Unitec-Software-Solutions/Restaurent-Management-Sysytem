<?php

require_once 'vendor/autoload.php';

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';

// Set up environment for console
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 CHECKING ADMIN LOGIN CREDENTIALS\n";
echo "===================================\n";

// Check if admin exists
$admin = App\Models\Admin::where('email', 'superadmin@rms.com')->first();

if (!$admin) {
    echo "❌ Super admin account not found!\n";
    echo "Creating super admin account...\n";
    
    $admin = App\Models\Admin::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@rms.com',
        'password' => Illuminate\Support\Facades\Hash::make('password123'),
        'is_super_admin' => true,
        'is_active' => true,
    ]);
    
    echo "✅ Super admin created successfully!\n";
} else {
    echo "✅ Super admin found: {$admin->name}\n";
}

echo "\n📋 Admin Details:\n";
echo "  👤 Name: {$admin->name}\n";
echo "  📧 Email: {$admin->email}\n";
echo "  🔐 Super Admin: " . ($admin->is_super_admin ? 'Yes' : 'No') . "\n";
echo "  ✅ Active: " . ($admin->is_active ? 'Yes' : 'No') . "\n";

// Test password verification
echo "\n🔐 PASSWORD VERIFICATION:\n";
echo "Testing 'password123': ";
if (Illuminate\Support\Facades\Hash::check('password123', $admin->password)) {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT\n";
}

echo "Testing 'password': ";
if (Illuminate\Support\Facades\Hash::check('password', $admin->password)) {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT\n";
}

echo "\n🔑 LOGIN CREDENTIALS:\n";
echo "Email: superadmin@rms.com\n";
echo "Password: password123\n";
echo "\n🌐 Admin Login URL: /admin/login\n";
