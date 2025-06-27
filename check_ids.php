<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Organization;
use App\Models\Branch;
use App\Models\ItemCategory;

echo "Current Organizations:\n";
$orgs = Organization::all(['id', 'name']);
foreach($orgs as $org) {
    echo "  ID: {$org->id} - {$org->name}\n";
}

echo "\nCurrent Branches:\n";
$branches = Branch::all(['id', 'name', 'organization_id']);
foreach($branches as $branch) {
    echo "  ID: {$branch->id} - {$branch->name} (org: {$branch->organization_id})\n";
}

echo "\nCurrent Item Categories:\n";
$categories = ItemCategory::all(['id', 'name']);
foreach($categories as $cat) {
    echo "  ID: {$cat->id} - {$cat->name}\n";
}

// Check if any item masters exist
$itemCount = \App\Models\ItemMaster::count();
echo "\nCurrent Item Masters count: {$itemCount}\n";
