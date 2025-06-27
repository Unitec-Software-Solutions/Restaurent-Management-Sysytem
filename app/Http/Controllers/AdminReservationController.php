<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Table;
use App\Traits\Exportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmed;
use App\Mail\ReservationRejected;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class AdminReservationController extends Controller
{
    use Exportable;
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        
        // Get filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $branchId = $request->input('branch_id');
        $phone = $request->input('phone');
        $export = $request->input('export');

        // Date filter setup
        if (!$startDate && !$endDate) {
            $startDate = now()->startOfDay()->toDateString();
            $endDate = now()->addDays(30)->toDateString();
        }

        // Base query with user permissions
        $query = \App\Models\Reservation::with(['branch', 'organization', 'steward', 'tables']);
        
        if ($admin->is_super_admin) { 
            // Super admin can see all reservations
        } elseif ($admin->branch_id) {
            $query->where('branch_id', $admin->branch_id);
        } elseif ($admin->organization_id) {
            $query->where('organization_id', $admin->organization_id);
        } else {
            $reservations = collect()->paginate(20);
            return view('admin.reservations.index', compact('reservations'));
        }

        // Apply filters
        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($phone) {
            $query->where('phone', 'like', "%{$phone}%");
        }

        $query->orderBy('date', 'desc')->orderBy('start_time', 'desc');

        // Apply filters and search for potential export
        $query = $this->applyFiltersToQuery($query, $request);

        // Handle export
        if ($request->has('export')) {
            return $this->exportToExcel($request, $query, 'reservations_export.xlsx', [
                'ID', 'Customer Name', 'Phone', 'Email', 'Date', 'Time', 'People', 'Status', 'Branch', 'Tables', 'Created At'
            ]);
        }

        $reservations = $query->paginate(20);

        // Get filter options
        $branches = \App\Models\Branch::where('is_active', true)->get();
        $stewards = \App\Models\Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('is_active', true)
            ->get();

        $filters = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'branchId' => $branchId,
            'phone' => $phone,
        ];

        return view('admin.reservations.index', compact('reservations', 'branches', 'stewards', 'filters'));
    }

    /**
     * Get searchable columns for reservations
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'phone', 'email'];
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
        
        // Load admin's branch relationship if not already loaded
        if (!$admin->relationLoaded('branch')) {
            $admin->load('branch');
        }
        
        // Validate admin has a branch assigned
        if (!$admin->branch_id || !$admin->branch) {
            return redirect()->route('admin.reservations.index')
                ->with('error', 'You must be assigned to a branch to edit reservations.');
        }
        
        // Load reservation relationships
        $reservation->load(['branch', 'tables', 'steward']); 

        // Use null-safe operator for branch access
        $branchId = $admin->branch?->id;
        if (!$branchId) {
            return redirect()->route('admin.reservations.index')
                ->with('error', 'Invalid branch assignment. Please contact administrator.');
        }

        $tables = Table::where('branch_id', $branchId)->get();
        $assignedTableIds = $reservation->tables->pluck('id')->toArray();
        $availableTableIds = $tables->pluck('id')->toArray();

        return view('admin.reservations.edit', compact(
            'reservation', 'tables', 'assignedTableIds', 'availableTableIds'
        ));
    }

public function update(Request $request, Reservation $reservation)
{
    if ($reservation->branch_id !== auth('admin')->user()->branch_id) {
        abort(403);
    }

    DB::beginTransaction();
    try {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|min:10|max:15',
            'email' => 'nullable|email|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_people' => 'required|integer|min:1',
            'assigned_table_ids' => 'nullable|array',
            'assigned_table_ids.*' => 'exists:tables,id',
            'status' => 'required|in:pending,confirmed,cancelled',
            'steward_id' => 'nullable|exists:employees,id', // use employees table if that's where stewards are
        ]);

        // Time validation
        if (\Carbon\Carbon::parse($validated['start_time'])->gt(\Carbon\Carbon::parse($validated['end_time']))) {
            return back()->withErrors(['end_time' => 'End time must be after start time']);
        }

        // Branch and fee logic
        $branch = $reservation->branch;
        $reservationFee = $branch->reservation_fee ?? 0;
        $cancellationFee = $branch->cancellation_fee ?? 0;

        // Capacity calculation (exclude current reservation)
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
        $availableCapacity = $branch->total_capacity - $reservedCapacity + $reservation->number_of_people;
        if ($availableCapacity < $validated['number_of_people']) {
            return back()->withErrors(['number_of_people' => 'Not enough capacity for the selected time slot.'])->withInput();
        }

        // Table conflict detection
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
                        ->where('reservations.id', '!=', $reservation->id)
                        ->where('reservations.status', '!=', 'cancelled');
                })
                ->pluck('number')
                ->toArray();
            if (count($conflictingTables) > 0) {
                return back()->withErrors(['assigned_table_ids' => 'The following tables are already reserved for the selected time: ' . implode(', ', $conflictingTables)])->withInput();
            }
        }

        // Save changes
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
            'status' => $validated['status'],
            'steward_id' => $validated['steward_id'], // ONLY THIS, not employee_id
        ]);
        $reservation->tables()->sync($validated['assigned_table_ids'] ?? []);

        // Comment out email notifications
        /*
        if ($reservation->wasChanged('status')) {
            if ($reservation->status === 'confirmed') {
                Mail::to($reservation->email)->send(new ReservationConfirmed($reservation));
            } elseif ($reservation->status === 'cancelled') {
                Mail::to($reservation->email)->send(new ReservationCancellationMail($reservation));
            } elseif ($reservation->status === 'rejected') {
                Mail::to($reservation->email)->send(new ReservationRejected($reservation));
            }
        }
        */

        DB::commit();
        return redirect()->route('admin.reservations.index')->with('success', 'Reservation updated.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Update failed: '.$e->getMessage()]);
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
        
        // Load branch relationship if not already loaded
        if (!$admin->relationLoaded('branch')) {
            $admin->load('branch');
        }
        
        // Validate admin has a branch assigned
        if (!$admin->branch_id || !$admin->branch) {
            return redirect()->route('admin.reservations.index')
                ->with('error', 'You must be assigned to a branch to create reservations.');
        }
        
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|min:10|max:15',
            'email' => 'nullable|email|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'number_of_people' => 'required|integer|min:1',
            'assigned_table_ids' => 'nullable|array',
            'assigned_table_ids.*' => 'exists:tables,id',
            'steward_id' => 'nullable|exists:employees,id',
        ]);

        // Use null-safe operator for branch access
        $branch = $admin->branch;
        
        // Validate branch operating hours with null-safe operators
        $branchOpenTime = $branch?->opening_time ? \Carbon\Carbon::parse($branch->opening_time)->format('H:i') : '00:00';
        $branchCloseTime = $branch?->closing_time ? \Carbon\Carbon::parse($branch->closing_time)->format('H:i') : '23:59';
        
        if ($validated['start_time'] < $branchOpenTime || $validated['end_time'] > $branchCloseTime) {
            return back()->withErrors(['time' => 'Reservation time must be within branch operating hours (' . $branchOpenTime . ' - ' . $branchCloseTime . ')'])->withInput();
        }
        // For same-day reservations, ensure start time is at least 30 minutes from now
        if ($validated['date'] === now()->format('Y-m-d')) {
            $minStartTime = now()->addMinutes(30)->format('H:i');
            if (\Carbon\Carbon::parse($validated['start_time'])->lt(\Carbon\Carbon::parse($minStartTime))) {
                return back()->withErrors(['start_time' => 'Start time must be at least 30 minutes from now.']);
            }
        }
        // Check capacity with null-safe operations
        $totalCapacity = $branch?->total_capacity ?? 0;
        
        if ($totalCapacity > 0) {
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
            
            $availableCapacity = $totalCapacity - $reservedCapacity;
            if ($availableCapacity < $validated['number_of_people']) {
                return back()->withErrors(['number_of_people' => 'Not enough capacity for the selected time slot.'])->withInput();
            }
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

        // Calculate fees with null-safe operators
        $reservationFee = $branch?->reservation_fee ?? 0;
        $cancellationFee = $branch?->cancellation_fee ?? 0;
        $branchId = $branch?->id;
        
        if (!$branchId) {
            return back()->withErrors(['error' => 'Invalid branch assignment. Cannot create reservation.'])->withInput();
        }

        $reservation = Reservation::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'number_of_people' => $validated['number_of_people'],
            'status' => 'pending',
            'branch_id' => $branchId,
            'reservation_fee' => $reservationFee,
            'cancellation_fee' => $cancellationFee,
            'steward_id' => $validated['steward_id'],
        ]);

        if (!empty($validated['assigned_table_ids'])) {
            $reservation->tables()->sync($validated['assigned_table_ids']);
        }

        // Redirect to the edit page for the new reservation
        return redirect()->route('admin.reservations.edit', $reservation)
            ->with('success', 'Reservation created successfully. You can now check in.');
    }

    public function create()
    {
        $admin = auth('admin')->user();
        
        // Load branch relationship if not already loaded
        if (!$admin->relationLoaded('branch')) {
            $admin->load('branch');
        }
        
        // Validate admin has a branch assigned
        if (!$admin->branch_id || !$admin->branch) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You must be assigned to a branch to create reservations.');
        }
        
        $branch = $admin->branch;
        
        // Use null-safe operators to prevent errors
        $branchId = $branch?->id;
        if (!$branchId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Invalid branch assignment. Please contact administrator.');
        }
        
        // Get tables for the branch with null-safe operations
        $tables = Table::where('branch_id', $branchId)->get();
        
        $availableTableIds = $tables->pluck('id')->toArray();
        $defaultPhone = $branch?->phone ?? '';
        $defaultDate = now()->toDateString();

        // Set default start time to now, end time to 2 hours later
        $now = now();
        $start_time = $now->format('H:i');
        $end_time = $now->copy()->addHours(2)->format('H:i');

        // Get next ID safely
        $nextId = (DB::table('reservations')->max('id') ?? 0) + 1;
        $defaultName = 'customer ' . $nextId;

        // Get stewards for the branch with null-safe operations
        $stewards = Employee::where('branch_id', $branchId)
            ->where('is_active', true)
            ->whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->get();

        return view('admin.reservations.create', compact(
            'tables', 'branch', 'availableTableIds', 'defaultPhone', 'defaultDate', 'defaultName', 'start_time', 'end_time', 'stewards'
        ));
    }

    // protected function sendNotification(Reservation $reservation, $method)
    // {
    //     if (in_array($method, ['email', 'both'])) {
    //         // Send email
    //         Mail::to($reservation->email)->send(new ReservationConfirmationMail($reservation));
    //     }

    //     if (in_array($method, ['sms', 'both'])) {
    //         // Send SMS (use a service like Twilio)
    //         SmsService::send($reservation->phone, "Your reservation has been confirmed.");
    //     }
    // }

    // protected function sendCancellationNotification(Reservation $reservation, $method)
    // {
    //     if (in_array($method, ['email', 'both'])) {
    //         // Send cancellation email
    //         Mail::to($reservation->email)->send(new ReservationCancellationMail($reservation));
    //     }

    //     if (in_array($method, ['sms', 'both'])) {
    //         SmsService::send($reservation->phone, "Your reservation has been cancelled. Reason: {$reservation->cancel_reason}");
    //     }
    // }
public function assignSteward(Request $request, Reservation $reservation)
{
    try {
        $validated = $request->validate([
            'steward_id' => 'required|exists:employees,id', // use employees table
        ]);

        $reservation->update(['steward_id' => $validated['steward_id']]);
        $employee = \App\Models\Employee::find($validated['steward_id']);

        return response()->json([
            'success' => true,
            'message' => 'Steward assigned successfully',
            'steward_name' => $employee ? $employee->name : null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to assign steward: ' . $e->getMessage()
        ], 500);
    }
}

public function checkIn(Reservation $reservation)
{
    if ($reservation->check_in_time) {
        return response()->json([
            'success' => false,
            'message' => 'Reservation already checked in'
        ], 400);
    }

    try {
        $reservation->update(['check_in_time' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Reservation checked in successfully',
            'check_in_time' => $reservation->fresh()->check_in_time->format('Y-m-d H:i:s'),
            'check_out_time' => $reservation->check_out_time
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to check in: ' . $e->getMessage()
        ], 500);
    }
}
public function checkOut(Reservation $reservation)
{
    if (!$reservation->check_in_time) {
        return response()->json([
            'success' => false,
            'message' => 'Reservation must be checked in before checkout'
        ], 400);
    }
    if ($reservation->check_out_time) {
        return response()->json([
            'success' => false,
            'message' => 'Reservation already checked out'
        ], 400);
    }
    try {
        $reservation->update(['check_out_time' => now()]);
        return response()->json([
            'success' => true,
            'message' => 'Reservation checked out successfully',
            'check_out_time' => $reservation->fresh()->check_out_time->format('Y-m-d H:i:s')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to check out: ' . $e->getMessage()
        ], 500);
    }
}
public function checkTableAvailability(Request $request)
{
    $request->validate([
        'date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

    $admin = auth('admin')->user();
    
    // Load branch relationship if not already loaded
    if (!$admin->relationLoaded('branch')) {
        $admin->load('branch');
    }
    
    // Validate admin has a branch assigned with null-safe operator
    $branchId = $admin->branch?->id;
    
    if (!$branchId) {
        return response()->json([
            'error' => 'No branch assigned to admin',
            'available_table_ids' => []
        ], 400);
    }

    $conflictingReservations = Reservation::where('branch_id', $branchId)
        ->where('date', $request->date)
        ->where('status', '!=', 'cancelled')
        ->where(function($query) use ($request) {
            $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                ->orWhere(function($q) use ($request) {
                    $q->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>=', $request->end_time);
                });
        })
        ->with('tables')
        ->get();

    $allTableIds = \App\Models\Table::where('branch_id', $branchId)->pluck('id');
    $reservedTableIds = $conflictingReservations->flatMap->tables->pluck('id')->unique();
    $availableTableIds = $allTableIds->diff($reservedTableIds)->values();

    return response()->json([
        'available_table_ids' => $availableTableIds
    ]);
}

/**
 * Show the order creation form for a reservation (admin).
 */
public function createOrder(Reservation $reservation)
{
    // You may want to pass reservation, branch, and any other needed data
    // For now, just redirect to the admin order creation view for this reservation
    // (You can customize this as needed for your order creation flow)
    return redirect()->route('admin.orders.reservations.create', ['reservation' => $reservation->id]);
}

/**
     * Export reservations based on filters
     */
    private function exportReservations($reservations, $format)
    {
        if ($format === 'csv') {
            $filename = 'reservations_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($reservations) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'ID', 'Name', 'Phone', 'Email', 'Date', 'Start Time', 'End Time',
                    'Number of People', 'Status', 'Branch', 'Steward', 'Tables',
                    'Reservation Fee', 'Cancellation Fee', 'Created At'
                ]);

                // CSV Data
                foreach ($reservations as $reservation) {
                    fputcsv($file, [
                        $reservation->id,
                        $reservation->name,
                        $reservation->phone,
                        $reservation->email,
                        $reservation->date,
                        $reservation->start_time,
                        $reservation->end_time,
                        $reservation->number_of_people,
                        ucfirst($reservation->status),
                        $reservation->branch?->name ?? 'N/A',
                        $reservation->steward?->name ?? 'N/A',
                        $reservation->tables?->pluck('number')->implode(', ') ?? 'N/A',
                        number_format($reservation->reservation_fee ?? 0, 2),
                        number_format($reservation->cancellation_fee ?? 0, 2),
                        $reservation->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return redirect()->back()->with('error', 'Invalid export format');
    }
}