<?php

use App\Models\Reservation;

// Test the reservation organization relationship
try {
    $reservation = Reservation::with('organization')->first();
    if ($reservation) {
        echo "Success: Reservation model can access organization relationship\n";
        if ($reservation->organization) {
            echo "Organization name: " . $reservation->organization->name . "\n";
        } else {
            echo "Reservation found but no organization linked\n";
        }
    } else {
        echo "No reservations found in database\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
