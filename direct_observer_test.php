// Test observer method directly
$org = App\Models\Organization::latest()->first();
echo "Using organization: " . $org->name . " (ID: " . $org->id . ")\n";

// Clean up any previous test data
$existingBranch = App\Models\Branch::where('name', 'Direct Observer Test')->first();
if ($existingBranch) {
    echo "Cleaning up existing test branch...\n";
    $existingBranch->delete();
}

// Create branch
$branch = new App\Models\Branch([
    'organization_id' => $org->id,
    'name' => 'Direct Observer Test',
    'slug' => 'direct-observer-test',
    'type' => 'cafe',
    'address' => '456 Direct Ave, Test City, TC 12348',
    'phone' => '+1-555-0191',
    'contact_person' => 'Direct Test Manager',
    'contact_person_designation' => 'Branch Manager',
    'contact_person_phone' => '+1-555-0192',
    'opening_time' => '07:00:00',
    'closing_time' => '21:00:00',
    'total_capacity' => 40,
    'reservation_fee' => 3.00,
    'cancellation_fee' => 1.50,
    'is_active' => true,
    'is_head_office' => false,
]);

// Save the branch (should trigger created event)
echo "Saving branch...\n";
$branch->save();

echo "Branch saved: " . $branch->name . " (ID: " . $branch->id . ")\n";

// Manual observer test
echo "Testing observer directly...\n";
$observer = new App\Observers\BranchObserver();
try {
    $observer->created($branch);
    echo "Observer called successfully!\n";
} catch (Exception $e) {
    echo "Observer failed: " . $e->getMessage() . "\n";
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
} else {
    echo "Kitchen stations NOT created!\n";
}

echo "Direct test completed!\n";
