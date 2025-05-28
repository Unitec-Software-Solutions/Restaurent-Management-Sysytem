<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function showReservationsByPhone(Request $request)
    {
        $phone = $request->input('phone');
        $reservations = [];
        if ($phone) {
            $reservations = Reservation::where('phone', $phone)->orderBy('date', 'desc')->get();
        }
        // Always pass both variables to the view
        return view('reservations.customer-dashboard', compact('reservations', 'phone'));
    }
}
