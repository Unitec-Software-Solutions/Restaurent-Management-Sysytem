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

class AdminReservationController extends Controller
{
    public function index()
    {
        $admin = auth()->user();
        $reservations = Reservation::where('branch_id', $admin->branch_id)->get();

        return view('admin.reservations.index', compact('reservations'));
    }

    public function pending()
    {
        $reservations = Reservation::with(['branch', 'user'])
            ->where('status', 'pending')
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
        $admin = auth()->user();

        if ($reservation->branch_id !== $admin->branch_id) {
            return redirect()->route('admin.reservations.index')->with('error', 'You are not authorized to edit this reservation.');
        }

        $tables = Table::where('branch_id', $admin->branch_id)->get();

        return view('admin.reservations.edit', compact('reservation', 'tables'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $admin = auth()->user();

        if ($reservation->branch_id !== $admin->branch_id) {
            return redirect()->route('admin.reservations.index')->with('error', 'You are not authorized to update this reservation.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'assigned_table_ids' => 'nullable|array',
            'assigned_table_ids.*' => 'exists:tables,id',
        ]);

        $reservation->update([
            'status' => $validated['status'],
            'assigned_table_ids' => $validated['assigned_table_ids'] ? json_encode($validated['assigned_table_ids']) : null,
        ]);

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation updated successfully.');
    }

    public function cancel(Reservation $reservation)
    {
        $admin = auth()->user();

        if ($reservation->branch_id !== $admin->branch_id) {
            return redirect()->route('admin.reservations.index')->with('error', 'You are not authorized to cancel this reservation.');
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation cancelled successfully.');
    }

    public function store(Request $request)
    {
        $admin = auth()->user();

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

        $reservation = Reservation::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'branch_id' => $admin->branch_id,
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'number_of_people' => $validated['number_of_people'],
            'comments' => $validated['comments'],
            'status' => 'pending',
            'created_by_admin_id' => $admin->id,
        ]);

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation created successfully.');
    }
}