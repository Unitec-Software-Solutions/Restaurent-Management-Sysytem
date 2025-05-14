<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationRejected;

class AdminReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['branch', 'user'])
            ->latest()
            ->paginate(10);

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
        $reservation->load(['branch', 'user']);
        return view('admin.reservations.edit', compact('reservation'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'no_of_people' => 'required|integer|min:1',
            'status' => 'required|in:pending,confirmed,rejected,cancelled',
            'comments' => 'nullable|string'
        ]);

        $reservation->update($validated);

        return redirect()
            ->route('admin.reservations.show', $reservation)
            ->with('success', 'Reservation has been updated.');
    }
} 