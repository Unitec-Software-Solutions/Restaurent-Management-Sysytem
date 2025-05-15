<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function create(Request $request)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('reservations.create', [
            'branches' => $branches,
            'request' => $request
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|min:10|max:15',
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

            // Directly create the reservation
            $reservation = Reservation::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'number_of_people' => $validated['number_of_people'],
                'comments' => $validated['comments'],
                'reservation_fee' => $branch->reservation_fee,
                'cancellation_fee' => $branch->cancellation_fee,
                'status' => 'pending',
                'branch_id' => $branch->id,
            ]);

            return redirect()->route('reservations.summary', $reservation)
                ->with('success', 'Your reservation has been created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Reservation creation failed: ' . $e->getMessage());
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

        // Calculate total capacity needed
        $totalCapacityNeeded = $people;
        
        // Calculate available capacity
        $totalCapacity = $branch->total_capacity;
        $reservedCapacity = $existingReservations->sum('number_of_people');
        $availableCapacity = $totalCapacity - $reservedCapacity;

        Log::info('Capacity calculation:', [
            'total_capacity' => $totalCapacity,
            'reserved_capacity' => $reservedCapacity,
            'available_capacity' => $availableCapacity,
            'needed_capacity' => $totalCapacityNeeded,
            'branch_id' => $branchId,
            'date' => $date,
            'time_slot' => $startTime . ' - ' . $endTime
        ]);

        return $availableCapacity >= $totalCapacityNeeded;
    }

    public function processPayment(Request $request, Reservation $reservation)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online_portal,qr_code,card,mobile_app'
        ]);
        
        $payment = Payment::create([
            'payable_type' => Reservation::class,
            'payable_id' => $reservation->id,
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
        return response('Reservation confirmed successfully.');
    }

    public function cancel(Reservation $reservation)
    {
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'This reservation cannot be cancelled.');
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('reservations.cancellation-success')
            ->with('success', 'Reservation cancelled successfully.');
    }

    public function cancellationSuccess()
    {
        return view('reservations.cancellation-success');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['branch', 'payments']);
        return view('reservations.show', [
            'reservation' => $reservation
        ]);
    }

    public function review(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:15',
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

    public function edit(Reservation $reservation)
    {
        // Only allow editing of pending reservations
        if ($reservation->status !== 'pending') {
            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Only pending reservations can be edited.');
        }

        // Load the branch relationship
        $reservation->load('branch');
        
        
        
        $branches = Branch::where('is_active', true)->get();
        return view('reservations.edit', compact('reservation', 'branches'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        // Only allow editing of pending reservations
        if ($reservation->status !== 'pending') {
            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Only pending reservations can be edited.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|min:10|max:15',
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

        try {
            $reservation->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'number_of_people' => $validated['number_of_people'],
                'comments' => $validated['comments'],
                'branch_id' => $branch->id,
            ]);

            return redirect()->route('reservations.show', $reservation)
                ->with('success', 'Reservation updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update reservation. Please try again.'])
                ->withInput();
        }
    }
}