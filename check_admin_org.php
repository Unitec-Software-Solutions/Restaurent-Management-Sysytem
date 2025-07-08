<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking all users and admins...\n";

try {
    // Check regular users
    $users = App\Models\User::all();
    echo "Total users: " . $users->count() . "\n";
    
    // Check admin users
    $admins = App\Models\Admin::all();
    echo "Total admins: " . $admins->count() . "\n\n";
    
    if ($admins->count() > 0) {
        echo "Admins:\n";
        foreach ($admins as $admin) {
            echo "ID: " . $admin->id . "\n";
            echo "Name: " . $admin->name . "\n";
            echo "Email: " . $admin->email . "\n";
            echo "Organization ID: " . ($admin->organization_id ?? 'NULL') . "\n";
            echo "Branch ID: " . ($admin->branch_id ?? 'NULL') . "\n";
            echo "Is super admin: " . ($admin->is_super_admin ? 'true' : 'false') . "\n";
            echo "---\n";
        }
    }
    
    // Check if there are any organizations
    $orgs = App\Models\Organization::all();
    echo "\nTotal organizations: " . $orgs->count() . "\n";
    
    if ($orgs->count() > 0) {
        echo "Organizations:\n";
        foreach ($orgs as $org) {
            echo "ID: " . $org->id . ", Name: " . $org->name . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
