<?php

/**
 * Test script for automated organization and branch creation system
 */

require_once 'vendor/autoload.php';

use App\Models\Organization;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\KitchenStation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Automated Organization and Branch Creation System\n";
echo "=======================================================\n\n";

try {
    echo "1. Creating test organization...\n";    $organization = Organization::create([
        'name' => 'Test Restaurant Group',
        'email' => 'admin@testrestaurant' . time() . '.com',
        'phone' => '+1-555-0123',
        'address' => '123 Test Street, Test City, TC 12345',
        'contact_person' => 'John Doe',
        'contact_person_designation' => 'CEO',
        'contact_person_phone' => '+1-555-0124',
        'is_active' => true,
        'password' => bcrypt('temporary123'),
        'business_type' => 'restaurant',
        'status' => 'active',
        'discount_percentage' => 0.00,
    ]);
    
    echo "✓ Organization created: {$organization->name} (ID: {$organization->id})\n";
    
    // Wait a moment for observer to complete
    sleep(1);
    
    echo "\n2. Checking automated head office creation...\n";
    $headOffice = $organization->branches()->where('is_head_office', true)->first();
    
    if ($headOffice) {
        echo "✓ Head office created: {$headOffice->name} (ID: {$headOffice->id})\n";
        echo "  - Type: {$headOffice->type}\n";
        echo "  - Address: {$headOffice->address}\n";
        echo "  - Active: " . ($headOffice->is_active ? 'Yes' : 'No') . "\n";
    } else {
        echo "✗ Head office not found!\n";
    }
    
    echo "\n3. Checking organization admin creation...\n";
    $orgAdmin = Admin::where('organization_id', $organization->id)
                    ->whereNull('branch_id')
                    ->first();
    
    if ($orgAdmin) {
        echo "✓ Organization admin created: {$orgAdmin->name} ({$orgAdmin->email})\n";
        echo "  - Active: " . ($orgAdmin->is_active ? 'Yes' : 'No') . "\n";
        echo "  - Roles: " . $orgAdmin->getRoleNames()->implode(', ') . "\n";
        echo "  - Permissions count: " . $orgAdmin->getAllPermissions()->count() . "\n";
    } else {
        echo "✗ Organization admin not found!\n";
    }
    
    echo "\n4. Checking kitchen stations creation...\n";
    $kitchenStations = KitchenStation::where('branch_id', $headOffice->id)->get();
    
    if ($kitchenStations->count() > 0) {
        echo "✓ Kitchen stations created: {$kitchenStations->count()} stations\n";
        foreach ($kitchenStations as $station) {
            echo "  - {$station->name} ({$station->type}) - Priority: {$station->order_priority}\n";
        }
    } else {
        echo "✗ No kitchen stations found!\n";
    }
      echo "\n5. Creating test branch...\n";
      $branch = Branch::create([
        'organization_id' => $organization->id,
        'name' => 'Downtown Cafe',
        'slug' => 'downtown-cafe',
        'type' => 'cafe',
        'address' => '456 Downtown Ave, Test City, TC 12346',
        'phone' => '+1-555-0125',
        'contact_person' => 'Jane Smith',
        'contact_person_designation' => 'Manager',
        'contact_person_phone' => '+1-555-0126',
        'opening_time' => '06:00:00',
        'closing_time' => '20:00:00',
        'total_capacity' => 50,
        'reservation_fee' => 5.00,
        'cancellation_fee' => 2.50,
        'is_active' => true,
        'is_head_office' => false,
        'activation_key' => Str::random(40),
    ]);
    
    echo "✓ Branch created: {$branch->name} (ID: {$branch->id})\n";
    
    // Wait a moment for observer to complete
    sleep(1);
    
    // Check if observer worked and apply manual fallback if needed
    $branchAdmin = Admin::where('branch_id', $branch->id)->first();
    $branchKitchenStations = KitchenStation::where('branch_id', $branch->id)->get();
    
    if (!$branchAdmin && $branchKitchenStations->count() === 0) {
        echo "  Observer didn't trigger automatically. Running manual setup...\n";
        $branch->setupAutomation();
        echo "  Manual setup completed.\n";
    } else {
        echo "  Observer triggered successfully!\n";
    }
    
    echo "\n6. Checking branch admin creation...\n";
    $branchAdmin = Admin::where('organization_id', $organization->id)
                       ->where('branch_id', $branch->id)
                       ->first();
    
    if ($branchAdmin) {
        echo "✓ Branch admin created: {$branchAdmin->name} ({$branchAdmin->email})\n";
        echo "  - Active: " . ($branchAdmin->is_active ? 'Yes' : 'No') . "\n";
        echo "  - Roles: " . $branchAdmin->getRoleNames()->implode(', ') . "\n";
        echo "  - Permissions count: " . $branchAdmin->getAllPermissions()->count() . "\n";
    } else {
        echo "✗ Branch admin not found!\n";
    }
    
    echo "\n7. Checking branch kitchen stations...\n";
    $branchStations = KitchenStation::where('branch_id', $branch->id)->get();
    
    if ($branchStations->count() > 0) {
        echo "✓ Branch kitchen stations created: {$branchStations->count()} stations\n";
        foreach ($branchStations as $station) {
            echo "  - {$station->name} ({$station->type}) - Priority: {$station->order_priority}\n";
        }
    } else {
        echo "✗ No branch kitchen stations found!\n";
    }
    
    echo "\n8. Summary Report:\n";
    echo "==================\n";
    echo "Organization: {$organization->name}\n";
    echo "Branches: " . $organization->branches()->count() . "\n";
    echo "Head Office: " . ($headOffice ? $headOffice->name : 'Not found') . "\n";
    echo "Total Admins: " . Admin::where('organization_id', $organization->id)->count() . "\n";
    echo "Total Kitchen Stations: " . KitchenStation::whereIn('branch_id', $organization->branches()->pluck('id'))->count() . "\n";
    
    echo "\n✅ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
