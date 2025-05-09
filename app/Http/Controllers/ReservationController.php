<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Branch;
use App\Models\User;
use App\Models\Payment;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function showPhoneCheck()
    {
        return view('reservations.check-phone');
    }

    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('phone_number', $request->phone)->first();
        
        if ($user) {
            return view('reservations.user-options', [
                'phone' => $request->phone,
                'user' => $user
            ]);
        }

        return view('reservations.guest-options', [
            'phone' => $request->phone
        ]);
    }

    public function proceedAsGuest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        return redirect()->route('reservations.create')
            ->with('phone', $request->phone);
    }

    public function proceedAsUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('phone_number', $request->phone)->first();
        
        if (!$user) {
            return redirect()->route('reservations.check-phone-form')
                ->withErrors(['phone' => 'User not found'])
                ->withInput();
        }

        return redirect()->route('login', ['phone' => $request->phone]);
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $phone = session('phone');
        
        return view('reservations.create', compact('branches', 'phone'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_people' => 'required|integer|min:1',
            'comments' => 'nullable|string|max:1000',
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);

        // Check if the time is within branch operating hours
        $branchOpenTime = Carbon::parse($branch->opening_time)->format('H:i');
        $branchCloseTime = Carbon::parse($branch->closing_time)->format('H:i');

        if ($validated['start_time'] < $branchOpenTime || $validated['end_time'] > $branchCloseTime) {
            return back()->withErrors(['time' => 'Reservation time must be within branch operating hours (' . $branchOpenTime . ' - ' . $branchCloseTime . ')']);
        }

        // Create the reservation
        try {
            $reservation = Reservation::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'number_of_people' => $validated['number_of_people'],
                'comments' => $validated['comments'],
                'reservation_fee' => $branch->reservation_fee,
                'cancellation_fee' => $branch->cancellation_fee,
                'status' => 'pending',
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
            ]);

            // Store phone in session for guest users
            if (!auth()->check()) {
                session(['phone' => $validated['phone']]);
            }

            return redirect()->route('reservations.summary', $reservation)
                ->with('success', 'Your reservation has been submitted and is pending confirmation.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to create reservation. Please try again.'])
                ->withInput();
        }
    }

    private function checkTableAvailability($date, $startTime, $endTime, $people, $branchId)
    {
        // Get all tables in the branch
        $branch = Branch::with('tables')->findOrFail($branchId);
        
        // Get existing reservations for the time slot
        $existingReservations = Reservation::where('branch_id', $branchId)
            ->where('date', $date)
            ->where(function($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->where('status', '!=', 'cancelled')
            ->get();

        // Get all tables in the branch
        $tables = $branch->tables;
        
        // Calculate total capacity needed
        $totalCapacityNeeded = $people;
        
        // Calculate available capacity
        $totalCapacity = $branch->total_capacity;
        $reservedCapacity = $existingReservations->sum('number_of_people');
        $availableCapacity = $totalCapacity - $reservedCapacity;

        // If total capacity is not enough, return false
        if ($availableCapacity < $totalCapacityNeeded) {
            return false;
        }

        // Check if we have tables that can accommodate the party
        $availableTables = $tables->filter(function($table) use ($existingReservations, $startTime, $endTime) {
            // Check if table is already reserved for this time slot
            $isReserved = $existingReservations->contains(function($reservation) use ($table, $startTime, $endTime) {
                return $reservation->table_id === $table->id;
            });
            
            return !$isReserved;
        });

        // Try to find a single table that can accommodate the party
        $singleTable = $availableTables->first(function($table) use ($totalCapacityNeeded) {
            return $table->capacity >= $totalCapacityNeeded;
        });

        if ($singleTable) {
            return true;
        }

        // If no single table is available, try to combine tables
        $remainingCapacity = $totalCapacityNeeded;
        $usedTables = collect();

        foreach ($availableTables->sortByDesc('capacity') as $table) {
            if ($remainingCapacity <= 0) {
                break;
            }
            
            $usedTables->push($table);
            $remainingCapacity -= $table->capacity;
        }

        // If we have enough tables to accommodate the party
        return $remainingCapacity <= 0;
    }

    public function processPayment(Request $request, Reservation $reservation)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online_portal,qr_code,card,mobile_app'
        ]);
        
        $payment = Payment::create([
            'payable_type' => Reservation::class,
            'payable_id' => $reservation->id,
            'user_id' => auth()->id(),
            'amount' => $reservation->reservation_fee,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'payment_reference' => 'RES-' . $reservation->id . '-' . time(),
            'is_active' => true,
            'notes' => 'Reservation payment'
        ]);
        
        return redirect()->route('reservations.summary', $reservation)
            ->with('success', 'Payment processed successfully.');
    }

    public function summary(Reservation $reservation)
    {
        $reservation->load(['branch', 'payments']);
        return view('reservations.summary', compact('reservation'));
    }

    public function confirm(Reservation $reservation)
    {
        $reservation->update(['status' => 'confirmed']);
        
        // Send confirmation email
        // TODO: Implement email sending
        
        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservation confirmed successfully.');
    }

    /**
     * Cancel a reservation.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Reservation $reservation)
    {
        // Check if user is authorized to cancel this reservation
        if (!auth()->check() && $reservation->phone !== session('phone')) {
            return redirect()->route('home')
                ->with('error', 'You are not authorized to cancel this reservation.');
        }

        // Only allow cancellation of pending or confirmed reservations
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('reservations.cancellation-success')
            ->with('success', 'Reservation cancelled successfully.');
    }

    /**
     * Show cancellation success page.
     *
     * @return \Illuminate\View\View
     */
    public function cancellationSuccess()
    {
        return view('reservations.cancellation-success');
    }

    public function waitlist(Request $request, Reservation $reservation)
    {
        $reservation->update([
            'status' => 'waitlisted',
            'notify_when_available' => true
        ]);

        return redirect()->route('reservations.index')
            ->with('success', 'You have been added to the waitlist. We will notify you when a table becomes available.');
    }

    public function joinWaitlist(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'date' => 'required|date|after_or_equal:today',
            'preferred_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    $branch = Branch::findOrFail($request->branch_id);
                    $time = Carbon::createFromFormat('H:i', $value);
                    
                    // Check if time is within branch hours
                    $openTime = Carbon::createFromFormat('H:i', $branch->opening_time);
                    $closeTime = Carbon::createFromFormat('H:i', $branch->closing_time);
                    
                    if ($time < $openTime || $time > $closeTime) {
                        $fail('Preferred time must be within branch operating hours (' . 
                              $branch->opening_time . ' - ' . $branch->closing_time . ')');
                    }
                    
                    // If date is today, time must be in the future
                    if ($request->date === Carbon::now()->format('Y-m-d') && 
                        $time->format('H:i') <= Carbon::now()->format('H:i')) {
                        $fail('Preferred time must be in the future for today\'s date');
                    }
                }
            ],
            'number_of_people' => 'required|integer|min:1',
            'branch_id' => 'required|exists:branches,id',
            'comments' => 'nullable|string|max:500'
        ]);
        
        // Add user_id if authenticated
        if (auth()->check()) {
            $validated['user_id'] = auth()->id();
        }
        
        // Create waitlist entry
        $waitlist = Waitlist::create($validated);
        
        // Send notification to admin about new waitlist entry
        // TODO: Implement notification
        
        return redirect()->back()->with('success', 
            'You have been added to the waitlist. We will notify you when a table becomes available.');
    }

    // Admin methods
    public function adminIndex()
    {
        $reservations = Reservation::with(['branch', 'user'])
            ->latest()
            ->paginate(10);
        return view('admin.reservations.index', compact('reservations'));
    }

    public function pendingReservations()
    {
        $reservations = Reservation::with(['branch', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);
        return view('admin.reservations.pending', compact('reservations'));
    }

    /**
     * Display the specified reservation.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\View\View
     */
    public function show(Reservation $reservation)
    {
        // Load the related branch and payments
        $reservation->load(['branch', 'payments']);

        // For admin users, always allow access
        if (auth()->check() && auth()->user()->isAdmin()) {
            return view('reservations.show', [
                'reservation' => $reservation
            ]);
        }

        // For regular users, check ownership
        if (auth()->check() && auth()->user()->id === $reservation->user_id) {
            return view('reservations.show', [
                'reservation' => $reservation
            ]);
        }

        // For guests, check phone number
        if (!$reservation->user_id && $reservation->phone === session('phone')) {
            return view('reservations.show', [
                'reservation' => $reservation
            ]);
        }

        // If none of the above conditions are met, redirect to home with error
        return redirect()->route('home')->with('error', 'You are not authorized to view this reservation.');
    }

    public function review(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_people' => 'required|integer|min:1',
            'comments' => 'nullable|string|max:1000',
        ]);

        $branch = Branch::findOrFail($validated['branch_id']);

        // Get branch times and convert to time string for comparison
        $branchOpenTime = Carbon::parse($branch->opening_time)->format('H:i');
        $branchCloseTime = Carbon::parse($branch->closing_time)->format('H:i');

        // Validate against branch operating hours
        if ($validated['start_time'] < $branchOpenTime || $validated['end_time'] > $branchCloseTime) {
            return back()->withErrors(['time' => 'Reservation time must be within branch operating hours (' . $branchOpenTime . ' - ' . $branchCloseTime . ')']);
        }

        // For same-day reservations, ensure start time is at least 30 minutes from now
        if ($validated['date'] === now()->format('Y-m-d')) {
            $minStartTime = now()->addMinutes(30)->format('H:i');
            if ($validated['start_time'] < $minStartTime) {
                return back()->withErrors(['time' => 'For same-day reservations, start time must be at least 30 minutes from now.']);
            }
        }

        return view('reservations.review', [
            'request' => $request,
            'branch' => $branch
        ]);
    }

    public function edit(Request $request)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('reservations.edit', compact('branches'));
    }
} 