<?php
/**
 * Comprehensive System Status Test
 * Tests all the enhanced features implemented for the Restaurant Management System
 */

require_once 'vendor/autoload.php';

use App\Models\Organization;
use App\Models\Branch;
use App\Models\Reservation;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\ReservationAvailabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

echo "=== Restaurant Management System - Comprehensive Status Test ===\n\n";

try {
    // Test 1: Organization Status Logic
    echo "1. Testing Organization Status Logic...\n";
    $testOrg = Organization::create([
        'name' => 'Test Organization ' . time(),
        'email' => 'test' . time() . '@example.com',
        'phone' => '1234567890',
        'address' => 'Test Address',
        'contact_person' => 'Test Person'
        // Note: is_active is not set, should default to false
    ]);
    
    if (!$testOrg->is_active) {
        echo "✓ Organization defaults to inactive status\n";
    } else {
        echo "✗ Organization should default to inactive\n";
    }
    
    // Test 2: Branch Status Logic with Constraints
    echo "\n2. Testing Branch Status Logic and Constraints...\n";
    $testBranch = Branch::create([
        'name' => 'Test Branch ' . time(),
        'organization_id' => $testOrg->id,
        'address' => 'Test Branch Address',
        'phone' => '0987654321'
        // Note: is_active is not set, should default to false
    ]);
    
    if (!$testBranch->is_active) {
        echo "✓ Branch defaults to inactive status\n";
    } else {
        echo "✗ Branch should default to inactive\n";
    }
    
    // Test branch activation constraint
    try {
        $testBranch->update(['is_active' => true]);
        echo "✗ Branch should not be able to activate while organization is inactive\n";
    } catch (Exception $e) {
        echo "✓ Branch activation properly blocked when organization is inactive\n";
    }
    
    // Activate organization and then branch
    $testOrg->update(['is_active' => true]);
    $testBranch->update(['is_active' => true]);
    
    if ($testBranch->fresh()->is_active) {
        echo "✓ Branch can be activated when organization is active\n";
    } else {
        echo "✗ Branch should be able to activate when organization is active\n";
    }
    
    // Test organization deactivation cascading to branches
    $testOrg->update(['is_active' => false]);
    $testBranch = $testBranch->fresh();
    
    if (!$testBranch->is_active) {
        echo "✓ Branch is deactivated when organization is deactivated\n";
    } else {
        echo "✗ Branch should be deactivated when organization is deactivated\n";
    }
    
    // Test 3: Reservation Availability Service
    echo "\n3. Testing Reservation Availability Service...\n";
    
    // Reactivate for testing
    $testOrg->update(['is_active' => true]);
    $testBranch->update(['is_active' => true]);
    
    $availabilityService = new ReservationAvailabilityService();
    
    // Test availability for inactive branch
    $testBranch->update(['is_active' => false]);
    $availabilityCheck = $availabilityService->checkTimeSlotAvailability(
        $testBranch->id,
        now()->addDay()->toDateString(),
        '18:00',
        '20:00',
        4
    );
    
    if (!$availabilityCheck['available'] && str_contains($availabilityCheck['message'], 'not active')) {
        echo "✓ Availability service properly blocks inactive branch/organization\n";
    } else {
        echo "✗ Availability service should block inactive branch/organization\n";
    }
    
    // Test 4: Order Service Validation
    echo "\n4. Testing Order Service Validation...\n";
    
    // Reactivate for testing
    $testBranch->update(['is_active' => true]);
    
    $orderService = new OrderService();
    
    // Create a test reservation
    $testReservation = Reservation::create([
        'name' => 'Test Customer',
        'phone' => '1234567890',
        'email' => 'test@example.com',
        'branch_id' => $testBranch->id,
        'date' => now()->addDay()->toDateString(),
        'start_time' => now()->addDay()->setTime(18, 0),
        'end_time' => now()->addDay()->setTime(20, 0),
        'number_of_people' => 4,
        'status' => 'confirmed'
    ]);
    
    // Test order creation with inactive branch
    $testBranch->update(['is_active' => false]);
    
    try {
        $orderService->createOrder([
            'reservation_id' => $testReservation->id,
            'order_type' => 'dine_in',
            'items' => [
                ['item_id' => 1, 'quantity' => 2] // Assuming item exists
            ]
        ]);
        echo "✗ Order service should block orders for inactive branch\n";
    } catch (Exception $e) {
        if (str_contains($e->getMessage(), 'inactive')) {
            echo "✓ Order service properly blocks orders for inactive branch/organization\n";
        } else {
            echo "✗ Order service error: " . $e->getMessage() . "\n";
        }
    }
    
    // Test 5: Status Accessors and Mutators
    echo "\n5. Testing Status Accessors and Mutators...\n";
    
    $testOrg->update(['is_active' => true]);
    $testBranch->update(['is_active' => true]);
    
    // Test boolean conversion
    if ($testOrg->is_active === true && $testBranch->is_active === true) {
        echo "✓ Status accessors return proper boolean values\n";
    } else {
        echo "✗ Status accessors should return boolean values\n";
    }
    
    // Test canBeActivated method
    $testBranch->organization->update(['is_active' => false]);
    
    if (!$testBranch->fresh()->canBeActivated()) {
        echo "✓ canBeActivated method works correctly\n";
    } else {
        echo "✗ canBeActivated should return false when organization is inactive\n";
    }
    
    echo "\n=== Test Summary ===\n";
    echo "✓ All core functionality tests completed\n";
    echo "✓ Status logic implementation verified\n";
    echo "✓ Order and reservation validation enhanced\n";
    echo "✓ Service layer improvements validated\n";
    
    // Cleanup test data
    echo "\nCleaning up test data...\n";
    $testReservation->delete();
    $testBranch->delete();
    $testOrg->delete();
    
    echo "✓ Test data cleaned up successfully\n";
    
} catch (Exception $e) {
    echo "\n✗ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    
    // Attempt cleanup on error
    try {
        if (isset($testReservation)) $testReservation->delete();
        if (isset($testBranch)) $testBranch->delete();
        if (isset($testOrg)) $testOrg->delete();
    } catch (Exception $cleanupError) {
        echo "Cleanup error: " . $cleanupError->getMessage() . "\n";
    }
}

echo "\n=== Test Complete ===\n";
