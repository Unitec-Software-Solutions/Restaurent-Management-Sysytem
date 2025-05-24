<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Branch;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationRejected;
use App\Mail\ReservationConfirmationMail;
use App\Mail\ReservationCancellationMail;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;

class AdminReservationController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        if (!$admin->branch || !$admin->branch->id) {
            return redirect()->route('admin.dashboard')->with('error', 'Branch information is missing for this user.');
        }

        $query = Reservation::with(['tables', 'branch', 'user'])
            ->where('branch_id', $admin->branch->id);

        if (request('phone')) {
            $query->where('phone', request('phone'));
        }

        $reservations = $query->get();

        return view('admin.reservations.index', compact('reservations'));
    }

    public function pending()
    {
        $admin = auth('admin')->user();
        $reservations = Reservation::with(['branch', 'user'])
            ->where('status', 'pending')
            ->where('branch_id', $admin->branch->id)
            ->latest()
            ->paginate(10);

        return view('admin.reservations.pending', compact('reservations'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['branch', 'user']);
        return view('admin.reservations.show', compact('reservation'));
    }

    public function confirm(Reservation $reservation)
    {
        $reservation->update(['status' => 'confirmed']);

        // Send confirmation email
        Mail::to($reservation->customer_email)
            ->send(new ReservationConfirmed($reservation));

        return redirect()
            ->back()
            ->with('success', 'Reservation confirmed and notification sent to customer.');
    }

    public function reject(Reservation $reservation)
    {
        $reservation->update(['status' => 'rejected']);

        // Handle payment refund if payment exists
        if ($reservation->payments()->exists()) {
            $payment = $reservation->payments()->latest()->first();
            
            // Create refund payment record
            Payment::create([
                'reservation_id' => $reservation->id,
                'amount' => -$payment->amount, // Negative amount for refund
                'payment_method' => $payment->payment_method,
                'status' => 'completed',
                'type' => 'refund'
            ]);

            // TODO: Implement actual refund logic with payment gateway
        }

        // Send rejection email
        Mail::to($reservation->customer_email)
            ->send(new ReservationRejected($reservation));

        return redirect()
            ->back()
            ->with('success', 'Reservation rejected and refund processed if applicable.');
    }

    public function edit(Reservation $reservation)
    {
        $admin = auth('admin')->user();

        if ($reservation->branch->id !== $admin->branch->id) {
            return redirect()->route('admin.reservations.index')->with('error', 'You are not authorized to edit this reservation.');
        }

        $tables = Table::where('branch_id', $admin->branch->id)->get();
        $assignedTableIds = $reservation->tables->pluck('id')->toArray();

        // Determine available tables for the reservation's date/time
        $reservedTableIds = Table::where('branch_id', $admin->branch->id)
            ->whereHas('reservations', function ($query) use ($reservation) {
                $query->where('reservations.date', $reservation->date)
                    ->where(function ($q) use ($reservation) {
                        $q->whereBetween('reservations.start_time', [$reservation->start_time, $reservation->end_time])
                          ->orWhereBetween('reservations.end_time', [$reservation->start_time, $reservation->end_time])
                          ->orWhere(function($q2) use ($reservation) {
                              $q2->where('reservations.start_time', '<=', $reservation->start_time)
                                 ->where('reservations.end_time', '>=', $reservation->end_time);
                          });
                    })
                    ->where('reservations.id', '!=', $reservation->id); // Exclude current reservation
            })
            ->pluck('tables.id')
            ->toArray();
        $availableTableIds = $tables->pluck('id')->diff($reservedTableIds)->merge($assignedTableIds)->unique()->toArray();

        return view('admin.reservations.edit', compact('reservation', 'tables', 'assignedTableIds', 'availableTableIds'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|min:10|max:15',
                'email' => 'nullable|email|max:255',
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'number_of_people' => 'required|integer|min:1',
                'assigned_table_ids' => 'nullable|array',
                'assigned_table_ids.*' => 'exists:tables,id',
            ]);

            // Validate branch operating hours
            $branch = $reservation->branch;
            $branchOpenTime = \Carbon\Carbon::parse($branch->opening_time)->format('H:i');
            $branchCloseTime = \Carbon\Carbon::parse($branch->closing_time)->format('H:i');
            if ($validated['start_time'] < $branchOpenTime || $validated['end_time'] > $branchCloseTime) {
                return back()->withErrors(['time' => 'Reservation time must be within branch operating hours (' . $branchOpenTime . ' - ' . $branchCloseTime . ')'])->withInput();
            }
            // For same-day reservations, ensure start time is at least 30 minutes from now
            if ($validated['date'] === now()->format('Y-m-d')) {
                $minStartTime = now()->addMinutes(30)->format('H:i');
                if ($validated['start_time'] < $minStartTime) {
                    return back()->withErrors(['time' => 'For same-day reservations, start time must be at least 30 minutes from now.'])->withInput();
                }
            }
            // Check capacity
            $reservedCapacity = $branch->reservations()
                ->where('date', $validated['date'])
                ->where(function($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhere(function($q) use ($validated) {
                            $q->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                        });
                })
                ->where('reservations.id', '!=', $reservation->id)
                ->where('reservations.status', '!=', 'cancelled')
                ->sum('number_of_people');
            $availableCapacity = $branch->total_capacity - $reservedCapacity;
            if ($availableCapacity < $validated['number_of_people']) {
                return back()->withErrors(['number_of_people' => 'Not enough capacity for the selected time slot.'])->withInput();
            }

            // Check if any selected tables are already reserved for the same date and overlapping time
            if (!empty($validated['assigned_table_ids'])) {
                $conflictingTables = Table::whereIn('id', $validated['assigned_table_ids'])
                    ->whereHas('reservations', function ($query) use ($validated, $reservation) {
                        $query->where('date', $validated['date'])
                            ->where(function ($q) use ($validated) {
                                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                                  ->orWhere(function($q2) use ($validated) {
                                      $q2->where('start_time', '<=', $validated['start_time'])
                                         ->where('end_time', '>=', $validated['end_time']);
                                  });
                            })
                            ->where('reservations.id', '!=', $reservation->id ?? null)
                            ->where('reservations.status', '!=', 'cancelled');
                    })
                    ->pluck('number')
                    ->toArray();
                if (count($conflictingTables) > 0) {
                    return back()->withErrors(['assigned_table_ids' => 'The following tables are already reserved for the selected time: ' . implode(', ', $conflictingTables)])->withInput();
                }
            }

            // Get branch and its fees
            $reservationFee = $branch && $branch->reservation_fee !== null ? $branch->reservation_fee : 0;
            $cancellationFee = $branch && $branch->cancellation_fee !== null ? $branch->cancellation_fee : 0;

            $reservation->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'number_of_people' => $validated['number_of_people'],
                'reservation_fee' => $reservationFee,
                'cancellation_fee' => $cancellationFee,
            ]);

            // Assign tables
            $reservation->tables()->sync($validated['assigned_table_ids'] ?? []);
            DB::commit();
            return redirect()->route('admin.reservations.index')->with('success', 'Reservation updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:1000',
            'send_notification' => 'nullable|in:none,email,sms,both',
        ]);

        $reservation->update([
            'status' => 'cancelled',
            'cancel_reason' => $validated['cancel_reason'],
        ]);

        // Send cancellation notification
        if ($validated['send_notification'] !== 'none') {
            $this->sendCancellationNotification($reservation, $validated['send_notification']);
        }

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation cancelled successfully.');
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:15',
            'email' => 'nullable|email|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_people' => 'required|integer|min:1',
            'assigned_table_ids' => 'nullable|array',
            'assigned_table_ids.*' => 'exists:tables,id',
        ]);

        // Validate branch operating hours
        $branch = $admin->branch;
        $branchOpenTime = \Carbon\Carbon::parse($branch->opening_time)->format('H:i');
        $branchCloseTime = \Carbon\Carbon::parse($branch->closing_time)->format('H:i');
        if ($validated['start_time'] < $branchOpenTime || $validated['end_time'] > $branchCloseTime) {
            return back()->withErrors(['time' => 'Reservation time must be within branch operating hours (' . $branchOpenTime . ' - ' . $branchCloseTime . ')'])->withInput();
        }
        // For same-day reservations, ensure start time is at least 30 minutes from now
        if ($validated['date'] === now()->format('Y-m-d')) {
            $minStartTime = now()->addMinutes(30)->format('H:i');
            if ($validated['start_time'] < $minStartTime) {
                return back()->withErrors(['time' => 'For same-day reservations, start time must be at least 30 minutes from now.'])->withInput();
            }
        }
        // Check capacity
        $reservedCapacity = $branch->reservations()
            ->where('date', $validated['date'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->where('reservations.status', '!=', 'cancelled')
            ->sum('number_of_people');
        $availableCapacity = $branch->total_capacity - $reservedCapacity;
        if ($availableCapacity < $validated['number_of_people']) {
            return back()->withErrors(['number_of_people' => 'Not enough capacity for the selected time slot.'])->withInput();
        }

        // Check if any selected tables are already reserved for the same date and overlapping time
        if (!empty($validated['assigned_table_ids'])) {
            $conflictingTables = Table::whereIn('id', $validated['assigned_table_ids'])
                ->whereHas('reservations', function ($query) use ($validated) {
                    $query->where('date', $validated['date'])
                        ->where(function ($q) use ($validated) {
                            $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                              ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                              ->orWhere(function($q2) use ($validated) {
                                  $q2->where('start_time', '<=', $validated['start_time'])
                                     ->where('end_time', '>=', $validated['end_time']);
                              });
                        })
                        ->where('reservations.id', '!=', $reservation->id ?? null)
                        ->where('reservations.status', '!=', 'cancelled');
                })
                ->pluck('number')
                ->toArray();
            if (count($conflictingTables) > 0) {
                return back()->withErrors(['assigned_table_ids' => 'The following tables are already reserved for the selected time: ' . implode(', ', $conflictingTables)])->withInput();
            }
        }

        $reservationFee = $branch && $branch->reservation_fee !== null ? $branch->reservation_fee : 0;
        $cancellationFee = $branch && $branch->cancellation_fee !== null ? $branch->cancellation_fee : 0;

        $reservation = Reservation::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'number_of_people' => $validated['number_of_people'],
            'status' => 'pending',
            'branch_id' => $branch->id,
            'reservation_fee' => $reservationFee,
            'cancellation_fee' => $cancellationFee,
        ]);

        // Assign tables to the reservation
        if (!empty($validated['assigned_table_ids'])) {
            $reservation->tables()->sync($validated['assigned_table_ids']);
        }

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation created successfully.');
    }

    public function create()
    {
        $admin = auth('admin')->user();
        $tables = Table::where('branch_id', $admin->branch->id)->get();
        $branch = $admin->branch;

        // For create, initially all tables are available (no reservation yet)
        $availableTableIds = $tables->pluck('id')->toArray();

        // Assign branch phone as default phone for reservation
        $defaultPhone = $branch->phone ?? '';
        // Assign current date as default date
        $defaultDate = now()->toDateString();

        return view('admin.reservations.create', compact('tables', 'branch', 'availableTableIds', 'defaultPhone', 'defaultDate'));
    }

    protected function sendNotification(Reservation $reservation, $method)
    {
        if (in_array($method, ['email', 'both'])) {
            // Send email
            Mail::to($reservation->email)->send(new ReservationConfirmationMail($reservation));
        }

        if (in_array($method, ['sms', 'both'])) {
            // Send SMS (use a service like Twilio)
            SmsService::send($reservation->phone, "Your reservation has been confirmed.");
        }
    }

    protected function sendCancellationNotification(Reservation $reservation, $method)
    {
        if (in_array($method, ['email', 'both'])) {
            // Send cancellation email
            Mail::to($reservation->email)->send(new ReservationCancellationMail($reservation));
        }

        if (in_array($method, ['sms', 'both'])) {
            // Send cancellation SMS
            SmsService::send($reservation->phone, "Your reservation has been cancelled. Reason: {$reservation->cancel_reason}");
        }
    }

}
