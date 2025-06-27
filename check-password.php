<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

echo "🔐 PASSWORD VERIFICATION\n";
echo "=======================\n";

$admin = Admin::where('email', 'superadmin@rms.com')->first();

if ($admin) {
    echo "Super admin found: {$admin->email}\n";
    echo "Password check (password123): " . (Hash::check('password123', $admin->password) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "Password check (password): " . (Hash::check('password', $admin->password) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "Stored hash: " . substr($admin->password, 0, 20) . "...\n";
    echo "Hash length: " . strlen($admin->password) . "\n";
    
    // Try to update password to a known value
    echo "\n🔧 Updating password to 'password123'...\n";
    $admin->password = Hash::make('password123');
    $admin->save();
    echo "✅ Password updated\n";
    
    // Verify it worked
    echo "New password check: " . (Hash::check('password123', $admin->password) ? 'MATCH' : 'NO MATCH') . "\n";
} else {
    echo "❌ Super admin not found\n";
}
