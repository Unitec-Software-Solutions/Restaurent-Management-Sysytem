<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\KitchenStation;
use App\Models\Branch;

$kitchenStations = KitchenStation::with('branch')->get();

echo "Kitchen Stations by Branch:\n";
foreach ($kitchenStations as $station) {
    echo "- {$station->branch->name}: {$station->name} ({$station->code})\n";
}

echo "\nTotal Kitchen Stations: " . $kitchenStations->count() . "\n";
echo "Total Branches: " . Branch::count() . "\n";

$stationsByBranch = KitchenStation::select('branch_id', 'name')->get()->groupBy('branch_id');
foreach ($stationsByBranch as $branchId => $stations) {
    $branch = Branch::find($branchId);
    echo "Branch ID {$branchId} ({$branch->name}): {$stations->count()} stations\n";
}
