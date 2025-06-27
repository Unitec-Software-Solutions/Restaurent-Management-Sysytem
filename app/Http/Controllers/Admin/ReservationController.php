<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReservationController extends Controller
{
    public function checkOut()
    {
        // TODO: Implement checkOut logic
        return view('admin.checkout');
    }

    /**
     * Assign steward to reservation
     */
    public function assignSteward(Request $request, $id)
    {
        return response()->json(['message' => 'Steward assigned']);
    }

    /**
     * Check in reservation
     */
    public function checkIn(Request $request, $id)
    {
        return response()->json(['message' => 'Checked in']);
    }

}
