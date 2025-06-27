<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Admin;

$orgAdmin = Admin::whereHas('roles', function($q) { 
    $q->where('name', 'LIKE', 'Organization Admin%'); 
})->first();

echo "Organization Admin permissions for organizations:\n";
$orgPermissions = $orgAdmin->permissions()->where('name', 'LIKE', 'organizations.%')->pluck('name');
foreach($orgPermissions as $perm) {
    echo "- $perm\n";
}

echo "\nOrganization Admin has organizations.view permission: ";
echo $orgAdmin->hasPermissionTo('organizations.view', 'admin') ? 'YES' : 'NO';
echo "\n";
