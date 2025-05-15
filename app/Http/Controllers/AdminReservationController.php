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

class AdminReservationController extends Controller
{
    public function index()
    {
        $admin = auth()->user();

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'You must be logged in to access this page.');
        }

        if (!$admin->branch_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Branch information is missing for this user.');
        }

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

        $reservation->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'number_of_people' => $validated['number_of_people'],
        ]);

        // Reassign tables to the reservation
        if (!empty($validated['assigned_table_ids'])) {
            $reservation->tables()->sync($validated['assigned_table_ids']);
        } else {
            $reservation->tables()->detach();
        }

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation updated successfully.');
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

        $reservation = Reservation::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'number_of_people' => $validated['number_of_people'],
            'status' => 'pending',
            'branch_id' => auth()->user()->branch_id,
        ]);

        // Assign tables to the reservation
        if (!empty($validated['assigned_table_ids'])) {
            $reservation->tables()->sync($validated['assigned_table_ids']);
        }

        return redirect()->route('admin.reservations.index')->with('success', 'Reservation created successfully.');
    }

    public function create()
    {
        $admin = auth()->user();

        // Fetch tables and branches for the admin's branch
        $tables = Table::where('branch_id', $admin->branch_id)->get();
        $branches = Branch::where('id', $admin->branch_id)->get();

        return view('admin.reservations.create', compact('tables', 'branches'));
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