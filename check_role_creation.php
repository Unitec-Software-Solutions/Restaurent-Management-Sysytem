<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;

echo "Manager roles:\n";
$managerRoles = Role::where('name', 'Manager')->get();
foreach ($managerRoles as $role) {
    echo "- Org: " . ($role->organization_id ?? 'N/A') . ", Created: " . $role->created_at . "\n";
}

echo "\nStaff Member roles:\n";
$staffRoles = Role::where('name', 'Staff Member')->get();
foreach ($staffRoles as $role) {
    echo "- Org: " . ($role->organization_id ?? 'N/A') . ", Created: " . $role->created_at . "\n";
}

echo "\nGuest User roles:\n";
$guestRoles = Role::where('name', 'Guest User')->get();
foreach ($guestRoles as $role) {
    echo "- Org: " . ($role->organization_id ?? 'N/A') . ", Created: " . $role->created_at . "\n";
}

echo "\nAll organization-specific roles:\n";
$orgRoles = Role::whereNotNull('organization_id')->get();
foreach ($orgRoles as $role) {
    echo "- {$role->name} (Org: {$role->organization_id}, Created: {$role->created_at})\n";
}
