<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Table;
use App\Models\RestaurantConfig;
use App\Services\ReservationAvailabilityService;
use App\Services\NotificationService;
use App\Enums\ReservationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationRejected;
use App\Mail\ReservationCancellationMail;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    protected $reservationAvailabilityService;
    protected $notificationService;

    public function __construct(
        ReservationAvailabilityService $reservationAvailabilityService,
        NotificationService $notificationService
    ) {
        $this->reservationAvailabilityService = $reservationAvailabilityService;
        $this->notificationService = $notificationService;
    }

    public function create(Request $request)
    {
        $branches = Branch::where('is_active', true)->get();
        
        // Check if we're in edit mode and populate from request
        $input = [];
        if ($request->has('edit_mode')) {
            $input = $request->only([
                'name', 'email', 'phone', 'branch_id', 
                'date', 'start_time', 'end_time', 
                'number_of_people', 'comments'
            ]);
        }
        
        return view('reservations.create', [
            'branches' => $branches,
            'input' => $input  // Pass input data to pre-fill form
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
                'type' => 'nullable|string|in:' . implode(',', ReservationType::values()),
                'preferred_contact' => 'nullable|string|in:email,sms',
            ]);

            $branch = Branch::with('organization')->findOrFail($validated['branch_id']);

            // Check branch and organization status
            if (!$branch->is_active || !$branch->organization->is_active) {
                return back()->withErrors(['branch' => 'Selected branch is not currently available for reservations.']);
            }

            // Find or create customer by phone
            $customer = Customer::findByPhone($validated['phone']);
            if (!$customer) {
                $customer = Customer::createFromPhone($validated['phone'], [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'preferred_contact' => $validated['preferred_contact'] ?? 'email',
                ]);
            } else {
                // Update customer info if provided
                $customer->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?: $customer->email,
                    'preferred_contact' => $validated['preferred_contact'] ?? $customer->preferred_contact,
                ]);
            }

            // Determine reservation type
            $reservationType = $validated['type'] ?? ReservationType::ONLINE->value;

            // Check availability
            $availabilityCheck = $this->reservationAvailabilityService->checkTimeSlotAvailability(
                $validated['branch_id'],
                $validated['date'],
                $validated['start_time'],
                $validated['end_time'],
                $validated['number_of_people']
            );

            if (!$availabilityCheck['available']) {
                // Simple error message - no waitlist option
                return back()->withErrors(['availability' => 'No tables available for your requested time. Please try another time slot.'])
                           ->withInput();
            }

            // Get reservation fee from configuration
            $reservationFee = RestaurantConfig::get('reservation_fee_' . $reservationType, 0);

            // Create reservation (model will handle setting customer_phone_fk and other logic via boot)
            $reservation = Reservation::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'customer_phone_fk' => $customer->phone,
                'branch_id' => $validated['branch_id'],
                'date' => $validated['date'],
                'start_time' => Carbon::parse($validated['date'] . ' ' . $validated['start_time']),
                'end_time' => Carbon::parse($validated['date'] . ' ' . $validated['end_time']),
                'number_of_people' => $validated['number_of_people'],
                'table_size' => $validated['number_of_people'], // Set table size
                'comments' => $validated['comments'],
                'type' => ReservationType::from($reservationType),
                'status' => 'pending',
                'reservation_fee' => $reservationFee,
                'user_id' => optional(auth())->id(),
            ]);

            // Assign tables if available
            if (!empty($availabilityCheck['table_assignment']['assigned_tables'])) {
                $tableIds = collect($availabilityCheck['table_assignment']['assigned_tables'])
                    ->pluck('id');
                $reservation->tables()->sync($tableIds);
            }

            DB::commit();

            // Send notification
            $this->notificationService->sendReservationConfirmation($reservation);

            Log::info('Reservation created successfully', [
                'reservation_id' => $reservation->id,
                'customer_phone' => $customer->phone,
                'branch_id' => $branch->id,
                'date' => $reservation->date,
                'time' => $reservation->start_time->format('H:i') . '-' . $reservation->end_time->format('H:i')
            ]);

            return redirect()->route('reservations.show', $reservation)
                           ->with('success', 'Reservation created successfully! You will receive a confirmation notification shortly.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation creation failed', [
                'error' => $e->getMessage(),
                'data' => $validated ?? []
            ]);
            
            return back()->withErrors(['error' => 'Failed to create reservation. Please try again.'])
                       ->withInput();
        }
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

        DB::beginTransaction();
        try {
            // Check if cancellation fee should be applied
            if ($reservation->shouldChargeCancellationFee()) {
                $cancellationFee = $reservation->calculateCancellationFee();
                $reservation->update([
                    'status' => 'cancelled',
                    'cancellation_fee' => $cancellationFee
                ]);
                
                // Create payment record for cancellation fee if applicable
                if ($cancellationFee > 0) {
                    Payment::create([
                        'payable_type' => Reservation::class,
                        'payable_id' => $reservation->id,
                        'amount' => $cancellationFee,
                        'payment_method' => 'system', // System-generated fee
                        'status' => 'pending',
                        'payment_reference' => 'CANCEL-FEE-' . $reservation->id . '-' . time(),
                        'notes' => 'Cancellation fee'
                    ]);
                }
            } else {
                $reservation->update(['status' => 'cancelled']);
            }

            DB::commit();

            // Send cancellation notification
            $this->notificationService->sendReservationCancellation($reservation);

            $message = 'Reservation cancelled successfully.';
            if (isset($cancellationFee) && $cancellationFee > 0) {
                $message .= ' A cancellation fee of $' . number_format($cancellationFee, 2) . ' applies.';
            }

            return redirect()->route('reservations.cancellation-success')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation cancellation failed', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to cancel reservation. Please try again.');
        }
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

        // Construct a reservation object and pass it to the view.
        $reservation = (object) [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'branch_id' => $validated['branch_id'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'number_of_people' => $validated['number_of_people'],
            'comments' => $validated['comments'],
        ];

        return view('reservations.review', [
            'reservation' => $reservation,
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
            // if ($reservation->wasChanged('status')) {
            //     if ($reservation->status === 'confirmed') {
            //         Mail::to($reservation->email)->send(new ReservationConfirmed($reservation));
            //     } elseif ($reservation->status === 'cancelled') {
            //         Mail::to($reservation->email)->send(new ReservationCancellationMail($reservation));
            //     } elseif ($reservation->status === 'rejected') {
            //         Mail::to($reservation->email)->send(new ReservationRejected($reservation));
            //     }
            // }

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