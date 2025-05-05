<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Table;
use App\Models\reservations;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
    public function start()
    {
        return view('reservations.start'); // Blade view with phone number form
    }

    public function checkPhone(Request $request)
    {
        $request->validate(['phone' => 'required']);
        $customer = Customer::where('phone', $request->phone)->first();

        if ($customer) {
            // Redirect to login or auto-login if already authenticated
            return redirect()->route('login')->with('phone', $request->phone);
        } else {
            // Redirect to signup or offer guest reservation
            return redirect()->route('signup')->with('phone', $request->phone);
        }
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Show reservation form (date, time, party size, type, etc.)
        $tables = Table::where('status', 'open')->get();
        $cancellationPolicies = $this->getCancellationPolicies();
        return view('reservations.create', compact('tables', 'cancellationPolicies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_size' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'reservation_type' => 'required|in:online,in-call,walk-in',
            // ... other fields ...
        ]);

        $reservation = new reservations($validated);
        $reservation->customer_id = auth()->id() ?? null;
        $reservation->status = 'pending';

        // Assign table or add to waitlist
        $table = $reservation->assignTable($validated['party_size']);
        if (!$table) {
            $reservation->addToWaitlist($reservation->customer_id, $validated['party_size']);
            // Optionally, trigger "Notify Me" logic here
        } else {
            $reservation->status = 'confirmed';
        }

        // Dynamic pricing logic
        $reservation->reservation_fee = $this->calculateReservationFee($reservation);

        $reservation->save();

        // Send confirmation
        $this->sendConfirmation($reservation);

        // Show summary before final confirmation
        return view('reservations.summary', compact('reservation', 'table'));
    }

    /**
     * Display the specified resource.
     */
    public function show(reservations $reservations)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(reservations $reservation)
    {
        // Show edit form
        return view('reservations.edit', compact('reservation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, reservations $reservation)
    {
        $validated = $request->validate([
            'party_size' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            // ... other fields ...
        ]);
        $reservation->update($validated);

        // Re-assign table if party size or time changed
        $reservation->updateTableStatus('open'); // Free old tables
        $table = $reservation->assignTable($validated['party_size']);
        if (!$table) {
            $reservation->addToWaitlist($reservation->customer_id, $validated['party_size']);
        } else {
            $reservation->status = 'confirmed';
        }
        $reservation->save();

        return redirect()->route('reservations.show', $reservation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(reservations $reservation)
    {
        $reservation->cancel();
        $reservation->updateTableStatus('open');
        // Handle refund/payment logic here
        return redirect()->route('reservations.index')->with('status', 'Reservation canceled.');
    }
    
    protected function calculateReservationFee($reservation)
    {
        // Example: $0 for walk-in, $10 for online/in-call
        if ($reservation->reservation_type === 'walk-in') {
            return 0;
        }
        return 10; // Or fetch from branch/organization settings
    }
    
    protected function getCancellationPolicies()
    {
        // Fetch and return policies from DB or config
        return [
            'high' => 20,
            'medium' => 10,
            'low' => 0,
        ];
    }
    
    protected function sendConfirmation($reservation)
    {
        // Send email/SMS using Laravel notifications or Mail
        if ($reservation->customer && $reservation->customer->email) {
            Mail::to($reservation->customer->email)->send(new \App\Mail\ReservationConfirmed($reservation));
        }
        // Add SMS logic if needed
    }
}
