// Test automation service directly
$org = App\Models\Organization::latest()->first();
echo "Using organization: " . $org->name . " (ID: " . $org->id . ")\n";

// Clean up any previous test data
$existingBranch = App\Models\Branch::where('name', 'Service Test Branch')->first();
if ($existingBranch) {
    echo "Cleaning up existing test branch...\n";
    $existingBranch->delete();
}

// Create branch
$branch = App\Models\Branch::create([
    'organization_id' => $org->id,
    'name' => 'Service Test Branch',
    'slug' => 'service-test-branch',
    'type' => 'cafe',
    'address' => '456 Service Ave, Test City, TC 12349',
    'phone' => '+1-555-0180',
    'contact_person' => 'Service Test Manager',
    'contact_person_designation' => 'Branch Manager',
    'contact_person_phone' => '+1-555-0181',
    'opening_time' => '07:00:00',
    'closing_time' => '21:00:00',
    'total_capacity' => 40,
    'reservation_fee' => 3.00,
    'cancellation_fee' => 1.50,
    'is_active' => true,
    'is_head_office' => false,
]);

echo "Branch created: " . $branch->name . " (ID: " . $branch->id . ")\n";

// Test service directly
echo "Testing BranchAutomationService directly...\n";
$service = new App\Services\BranchAutomationService();
try {
    $service->setupNewBranch($branch);
    echo "Service call successful!\n";
} catch (Exception $e) {
    echo "Service call failed: " . $e->getMessage() . "\n";
}

// Check results
$branchAdmin = App\Models\Admin::where('branch_id', $branch->id)->first();
if ($branchAdmin) {
    echo "Branch admin created: " . $branchAdmin->email . "\n";
} else {
    echo "Branch admin NOT created!\n";
}

$kitchenStations = App\Models\KitchenStation::where('branch_id', $branch->id)->get();
if ($kitchenStations->count() > 0) {
    echo "Kitchen stations created: " . $kitchenStations->count() . " stations\n";
    foreach ($kitchenStations as $station) {
        echo "  - " . $station->name . " (" . $station->type . ")\n";
    }
} else {
    echo "Kitchen stations NOT created!\n";
}

echo "Service test completed!\n";
