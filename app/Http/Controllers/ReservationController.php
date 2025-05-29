<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationRejected;
use App\Mail\ReservationCancellationMail;
use Illuminate\Support\Facades\Mail;
>>>>>>> de2fd5646ecd1d9c19a18ad92d4891d8a362f634

class ReservationController extends Controller
{
    public function index()
    {
        return view('reservations.index'); // Ensure this view exists
    }

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
        DB::beginTransaction();
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

            // For same-day reservations, ensure start time is at least 30 minutes from now
            if ($validated['date'] === now()->format('Y-m-d')) {
                $minStartTime = now()->addMinutes(30)->format('H:i');
                if ($validated['start_time'] < $minStartTime) {
                    return back()->withErrors(['time' => 'For same-day reservations, start time must be at least 30 minutes from now.']);
                }
            }

            // Enhanced: Use checkTableAvailability for robust capacity check
            if (!$this->checkTableAvailability(
                $validated['date'],
                $validated['start_time'],
                $validated['end_time'],
                $validated['number_of_people'],
                $branch->id
            )) {
                throw new \Exception('No available tables for selected time');
            }

            // Find available tables (for assignment)
            $tables = Table::where('branch_id', $branch->id)
                ->available($validated['date'], $validated['start_time'], $validated['end_time'])
                ->orderBy('capacity', 'asc')
                ->get();

            $required = $validated['number_of_people'];
            $selectedTables = [];
            $sum = 0;
            foreach ($tables as $table) {
                $selectedTables[] = $table;
                $sum += $table->capacity;
                if ($sum >= $required) break;
            }
            if ($sum < $required) {
                throw new \Exception('No available tables for selected time');
            }

            
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

            // Assign tables
            $reservation->tables()->sync(collect($selectedTables)->pluck('id'));

            DB::commit();
            return redirect()->route('reservations.summary', $reservation)
                ->with('success', 'Your reservation has been created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation creation failed: ' . $e->getMessage());
            return back()
                ->withErrors(['error' => $e->getMessage()])
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
        DB::beginTransaction();
        try {
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
            $reservation->update(['status' => 'confirmed']);
            DB::commit();
            return redirect()->route('reservations.summary', $reservation)
                ->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Payment failed']);
        }
    }

    public function summary(Reservation $reservation)
    {
        $reservation->load(['branch', 'payments']);
        return view('reservations.summary', compact('reservation'));
    }

    public function confirm(Reservation $reservation)
    {
        $reservation->update(['status' => 'confirmed']);
        return redirect()->route('reservations.summary', $reservation)
            ->with('success', 'Reservation confirmed successfully.');
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

            // Check if status changed and send the appropriate email
            if ($reservation->wasChanged('status')) {
                if ($reservation->status === 'confirmed') {
                    Mail::to($reservation->email)->send(new ReservationConfirmed($reservation));
                } elseif ($reservation->status === 'cancelled') {
                    Mail::to($reservation->email)->send(new ReservationCancellationMail($reservation));
                } elseif ($reservation->status === 'rejected') {
                    Mail::to($reservation->email)->send(new ReservationRejected($reservation));
                }
            }

            return redirect()->route('reservations.show', $reservation)
                ->with('success', 'Reservation updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update reservation. Please try again.'])
                ->withInput();
        }
    }

    public function payment(Reservation $reservation)
    {
        // You can customize this view as needed
        return view('reservations.payment', compact('reservation'));
    }
}