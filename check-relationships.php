<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Branch;
use App\Models\KitchenStation;

echo "Testing branch kitchen station relationships:\n";

$nonHeadOfficeBranches = Branch::where('is_head_office', false)->get();
foreach ($nonHeadOfficeBranches as $branch) {
    $kitchenStations = $branch->kitchenStations;
    $directCount = KitchenStation::where('branch_id', $branch->id)->count();
    
    echo "- Branch ID {$branch->id} ({$branch->name}):\n";
    echo "  - Relationship count: " . ($kitchenStations ? $kitchenStations->count() : 'null') . "\n";
    echo "  - Direct query count: {$directCount}\n";
    
    if ($directCount > 0) {
        $stations = KitchenStation::where('branch_id', $branch->id)->get();
        foreach ($stations as $station) {
            echo "    - {$station->name} ({$station->code})\n";
        }
    }
    echo "\n";
}
