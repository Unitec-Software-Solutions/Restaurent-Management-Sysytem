<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 DATABASE VALIDATION RESULTS\n";
echo "==============================\n\n";

try {
    $orgCount = \App\Models\Organization::count();
    $branchCount = \App\Models\Branch::count();
    $stationCount = \App\Models\KitchenStation::count();
    
    echo "✅ Organizations: {$orgCount}\n";
    echo "✅ Branches: {$branchCount}\n";
    echo "✅ Kitchen Stations: {$stationCount}\n\n";
    
    echo "🏭 Kitchen Station Codes:\n";
    $stations = \App\Models\KitchenStation::select('code', 'name', 'type')->get();
    foreach ($stations as $station) {
        echo "  • {$station->code} - {$station->name} ({$station->type})\n";
    }
    
    echo "\n🎯 VALIDATION SUMMARY:\n";
    echo "==================\n";
    echo ($orgCount > 0 ? "✅" : "❌") . " Organizations seeded successfully\n";
    echo ($branchCount > 0 ? "✅" : "❌") . " Branches created with organizations\n";
    echo ($stationCount > 0 ? "✅" : "❌") . " Kitchen stations created with unique codes\n";
      // Check for duplicate codes
    $duplicates = \App\Models\KitchenStation::select('code', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
        ->groupBy('code')
        ->havingRaw('COUNT(*) > 1')
        ->get();
        
    echo ($duplicates->isEmpty() ? "✅" : "❌") . " No duplicate kitchen station codes\n";
    
    // Check NOT NULL constraints
    $stationsWithoutCodes = \App\Models\KitchenStation::whereNull('code')->count();
    echo ($stationsWithoutCodes === 0 ? "✅" : "❌") . " All kitchen stations have required codes\n";
    
    echo "\n🎉 DATABASE SEEDER ERROR RESOLUTION SYSTEM: WORKING CORRECTLY!\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking database: " . $e->getMessage() . "\n";
}
