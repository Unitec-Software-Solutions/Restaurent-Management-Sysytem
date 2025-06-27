// Test branch creation through Laravel's Eloquent
$org = App\Models\Organization::latest()->first();
echo "Using organization: " . $org->name . " (ID: " . $org->id . ")\n";

// Clean up any previous test data
$existingBranch = App\Models\Branch::where('name', 'Tinker Test Branch')->first();
if ($existingBranch) {
    echo "Cleaning up existing test branch...\n";
    $existingBranch->delete();
}

// Create branch using Eloquent (should trigger observer)
echo "Creating branch using Eloquent::create...\n";
$branch = App\Models\Branch::create([
    'organization_id' => $org->id,
    'name' => 'Tinker Test Branch',
    'slug' => 'tinker-test-branch',
    'type' => 'cafe',
    'address' => '456 Tinker Ave, Test City, TC 12347',
    'phone' => '+1-555-0199',
    'contact_person' => 'Tinker Test Manager',
    'contact_person_designation' => 'Branch Manager',
    'contact_person_phone' => '+1-555-0198',
    'opening_time' => '07:00:00',
    'closing_time' => '21:00:00',
    'total_capacity' => 40,
    'reservation_fee' => 3.00,
    'cancellation_fee' => 1.50,
    'is_active' => true,
    'is_head_office' => false,
]);

echo "Branch created: " . $branch->name . " (ID: " . $branch->id . ")\n";

// Check if branch admin was created
$branchAdmin = App\Models\Admin::where('branch_id', $branch->id)->first();
if ($branchAdmin) {
    echo "Branch admin created: " . $branchAdmin->email . "\n";
} else {
    echo "Branch admin NOT created!\n";
}

// Check if kitchen stations were created
$kitchenStations = App\Models\KitchenStation::where('branch_id', $branch->id)->get();
if ($kitchenStations->count() > 0) {
    echo "Kitchen stations created: " . $kitchenStations->count() . " stations\n";
    foreach ($kitchenStations as $station) {
        echo "  - " . $station->name . " (" . $station->type . ")\n";
    }
} else {
    echo "Kitchen stations NOT created!\n";
}

echo "Test completed!\n";
