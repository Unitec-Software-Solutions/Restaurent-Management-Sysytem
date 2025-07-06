<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;

echo "Current roles in database:\n";
$roles = Role::all();

if ($roles->count() === 0) {
    echo "No roles found in database.\n";
} else {
    foreach ($roles as $role) {
        echo "- {$role->name} (ID: {$role->id}, Org: " . ($role->organization_id ?? 'N/A') . ", Branch: " . ($role->branch_id ?? 'N/A') . ")\n";
    }
}

echo "\nTotal roles: " . $roles->count() . "\n";
