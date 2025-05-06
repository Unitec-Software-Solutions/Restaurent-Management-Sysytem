<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Table;
use App\Models\reservations;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Branch;

class ReservationsController extends Controller
{
    public function start()
    {
        return view('reservations.start'); // Form to enter phone number
    }

    public function checkPhone(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|min:10|max:15|regex:/^[0-9]+$/'
        ], [
            'phone_number.required' => 'Please enter your phone number',
            'phone_number.min' => 'Phone number must be at least 10 digits',
            'phone_number.max' => 'Phone number must not exceed 15 digits',
            'phone_number.regex' => 'Phone number must contain only numbers'
        ]);

        $user = User::where('phone_number', $validated['phone_number'])->first();

        if ($user) {
            // User is registered
            if ($user->is_registered) {
                // Ask if they want to login or proceed as guest
                return view('reservations.ask_login', [
                    'phone_number' => $validated['phone_number'],
                    'user' => $user
                ]);
            } else {
                // User exists but not fully registered
                return view('reservations.ask_signup', [
                    'phone_number' => $validated['phone_number'],
                    'user' => $user
                ]);
            }
        } else {
            // New user - ask if they want to register or proceed as guest
            return view('reservations.ask_signup', [
                'phone_number' => $validated['phone_number']
            ]);
        }
    }

    public function proceedAsGuest(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|min:10|max:15|regex:/^[0-9]+$/'
        ]);

        // Create a temporary user if doesn't exist
        $user = User::firstOrCreate(
            ['phone_number' => $validated['phone_number']],
            [
                'name' => 'Guest',
                'email' => 'guest_' . $validated['phone_number'] . '@temporary.com',
                'password' => bcrypt(Str::random(10)),
                'is_registered' => false,
                'user_type' => 'customer'
            ]
        );

        // Log in the user
        Auth::login($user);

        // Redirect to choose action
        return redirect()->route('reservations.choose-action');
    }

    public function chooseAction()
    {
        return view('reservations.choose_action');
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
        return view('reservations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'party_size' => 'required|integer|min:1|max:20',
            'special_requests' => 'nullable|string|max:500'
        ]);

        // Combine date and time
        $reservation_datetime = Carbon::parse($validated['reservation_date'] . ' ' . $validated['reservation_time']);

        // Get the first branch
        $branch = Branch::first();
        if (!$branch) {
            return back()->with('error', 'No branch found. Please contact the restaurant administrator.');
        }

        // Create reservation
        $reservation = new reservations([
            'user_id' => auth()->id(),
            'customer_name' => auth()->user()->name,
            'customer_phone' => auth()->user()->phone_number,
            'customer_email' => auth()->user()->email,
            'branch_id' => $branch->id,
            'reservation_datetime' => $reservation_datetime,
            'party_size' => $validated['party_size'],
            'special_requests' => $validated['special_requests'],
            'status' => 'pending',
            'reservation_type' => 'online',
            'reservation_fee' => 0.00,
            'cancellation_fee' => 0.00,
            'is_waitlist' => false,
            'notify_when_available' => false,
            'is_active' => true
        ]);

        // Save reservation
        $reservation->save();

        // Redirect to summary
        return redirect()->route('reservation.summary', $reservation->id);
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

    public function summary($id)
    {
        $reservation = reservations::findOrFail($id);
        return view('reservations.summary', compact('reservation'));
    }

    public function confirm(Request $request, $id)
    {
        $reservation = reservations::findOrFail($id);
        
        // Update reservation status
        $reservation->status = 'confirmed';
        $reservation->save();

        // Send confirmation notification
        // TODO: Implement notification system

        return redirect()->route('home')->with('success', 'Your reservation has been confirmed!');
    }
}
