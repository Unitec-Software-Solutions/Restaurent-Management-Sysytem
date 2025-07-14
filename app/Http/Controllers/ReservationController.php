<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\RestaurantConfig;
use App\Services\ReservationAvailabilityService;
use App\Services\NotificationService;
use App\Enums\ReservationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Organization;

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
        try {
            $admin = auth('admin')->user();
            $isSuperAdmin = $admin && $admin->is_super_admin;

            // Get all active organizations (for super admin dropdown)
            $organizations = Organization::where('is_active', true)
                ->select('id', 'name', 'trading_name')
                ->orderBy('name')
                ->get()
                ->map(function($org) {
                    return [
                        'id' => $org->id,
                        'name' => $org->trading_name ?: $org->name
                    ];
                });

            $organization_id = null;
            $branch_id = null;
            $branches = collect();

            if ($isSuperAdmin) {
                // Super admin: allow selection, no default
                $organization_id = $request->get('organization_id');
                $branch_id = $request->get('branch_id');
                if ($organization_id) {
                    $branches = \App\Models\Branch::where('organization_id', $organization_id)->get();
                }
            } else if ($admin) {
                // Admin: auto-fill
                $organization_id = $admin->organization_id;
                $branch_id = $admin->branch_id;
                if ($organization_id) {
                    $branches = \App\Models\Branch::where('organization_id', $organization_id)->get();
                }
            }

            return view('reservations.create', compact(
                'organizations',
                'organization_id',
                'branch_id',
                'branches',
                'isSuperAdmin'
            ));

        } catch (\Exception $e) {
            Log::error('Error in reservation create: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Unable to load reservation form. Please try again.');
        }
    }



    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Debug: Log incoming request data to help diagnose validation issues
            Log::info('Reservation creation attempt', [
                'request_data' => $request->all()
            ]);

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
            ], [
                'name.required' => 'Name is required',
                'phone.required' => 'Phone number is required',
                'phone.min' => 'Phone number must be at least 10 digits',
                'branch_id.required' => 'Please select a restaurant branch',
                'branch_id.exists' => 'Selected branch is not valid',
                'date.required' => 'Reservation date is required',
                'date.after_or_equal' => 'Reservation date must be today or a future date',
                'start_time.required' => 'Start time is required',
                'start_time.date_format' => 'Invalid start time format',
                'end_time.required' => 'End time is required',
                'end_time.date_format' => 'Invalid end time format',
                'end_time.after' => 'End time must be after start time',
                'number_of_people.required' => 'Number of people is required',
                'number_of_people.min' => 'Number of people must be at least 1',
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Reservation validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return back()->withErrors($e->errors())->withInput()
                ->with('error', 'Please check the form and correct the highlighted errors.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Failed to create reservation: ' . $e->getMessage()])
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
        // (Removed as per user request)
        // if ($validated['date'] === now()->format('Y-m-d')) {
        //     $minStartTime = now()->addMinutes(30)->format('H:i');
        //     if ($validated['start_time'] < $minStartTime) {
        //         return back()->withErrors(['time' => 'For same-day reservations, start time must be at least 30 minutes from now.']);
        //     }
        // }

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

    /**
     * Get branches for a specific organization (API endpoint)
     *
     * @param int $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBranches($organizationId)
    {
        try {
            // Validate organization exists
            $organization = Organization::find($organizationId);

            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found',
                    'branches' => []
                ], 404);
            }

            // Get active branches for the organization
            $branches = Branch::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->select([
                    'id',
                    'name',
                    'address',
                    'phone',
                    'email',
                    'opening_time',
                    'closing_time',
                    'is_active',
                    'organization_id'
                ])
                ->orderBy('name')
                ->get();

            // Log the request for debugging
            Log::info('Branches fetched for organization', [
                'organization_id' => $organizationId,
                'organization_name' => $organization->name,
                'branches_count' => $branches->count(),
                'branches' => $branches->pluck('name')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Branches retrieved successfully',
                'branches' => $branches,
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching branches for organization', [
                'organization_id' => $organizationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch branches: ' . $e->getMessage(),
                'branches' => []
            ], 500);
        }
    }

    /**
     * Get available time slots for a branch on a specific date
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTimeSlots(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'date' => 'required|date|after_or_equal:today',
                'party_size' => 'required|integer|min:1|max:20'
            ]);

            $branch = Branch::find($request->branch_id);
            $date = Carbon::parse($request->date);
            $partySize = $request->party_size;

            // Check if branch is open on this date
            $dayOfWeek = $date->dayOfWeek;

            // Get available time slots (simplified logic)
            $openingTime = $branch->opening_time ?: '09:00';
            $closingTime = $branch->closing_time ?: '22:00';

            $timeSlots = $this->generateTimeSlots($openingTime, $closingTime, $date, $branch->id, $partySize);

            return response()->json([
                'success' => true,
                'message' => 'Time slots retrieved successfully',
                'time_slots' => $timeSlots,
                'branch_info' => [
                    'name' => $branch->name,
                    'opening_time' => $openingTime,
                    'closing_time' => $closingTime
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching time slots', [
                'request_data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch time slots: ' . $e->getMessage(),
                'time_slots' => []
            ], 500);
        }
    }

    /**
     * Generate available time slots for a branch
     *
     * @param string $openingTime
     * @param string $closingTime
     * @param Carbon $date
     * @param int $branchId
     * @param int $partySize
     * @return array
     */
    private function generateTimeSlots($openingTime, $closingTime, $date, $branchId, $partySize)
    {
        $slots = [];
        $interval = 30; // 30 minutes interval

        $start = Carbon::createFromFormat('H:i', $openingTime);
        $end = Carbon::createFromFormat('H:i', $closingTime);

        // If requesting for today, start from current time + 1 hour
        if ($date->isToday()) {
            $minTime = now()->addHour();
            if ($start->lt($minTime)) {
                $start = $minTime->copy()->minute(0)->second(0);
                if ($minTime->minute > 0) {
                    $start->addHour();
                }
            }
        }

        while ($start->lt($end->subHours(2))) { // Stop 2 hours before closing
            $timeString = $start->format('H:i');
            $displayTime = $start->format('g:i A');

            // Check availability (simplified - you can add more complex logic)
            $isAvailable = $this->isTimeSlotAvailable($branchId, $date->format('Y-m-d'), $timeString, $partySize);

            $slots[] = [
                'time' => $timeString,
                'display_time' => $displayTime,
                'available' => $isAvailable,
                'party_size' => $partySize
            ];

            $start->addMinutes($interval);
        }

        return $slots;
    }

    /**
     * Check if a time slot is available
     *
     * @param int $branchId
     * @param string $date
     * @param string $time
     * @param int $partySize
     * @return bool
     */
    private function isTimeSlotAvailable($branchId, $date, $time, $partySize)
    {
        // Simple availability check - you can enhance this with table capacity logic
        $existingReservations = Reservation::where('branch_id', $branchId)
            ->where('date', $date)
            ->where('start_time', $time)
            ->where('status', '!=', 'cancelled')
            ->count();

        // Assume max 5 reservations per time slot (you can make this configurable)
        return $existingReservations < 5;
    }
}
