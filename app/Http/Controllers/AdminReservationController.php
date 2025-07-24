<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\Table;
use App\Models\Branch;
use App\Models\Organization;
use App\Traits\Exportable;
use App\Enums\ReservationType;
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

        if (!$admin) {
            return redirect()->route('admin.login');
        }

        try {
            // Get filter parameters
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $status = $request->input('status');
            $branchId = $request->input('branch_id');
            $phone = $request->input('phone');
            
            // Base query with relationships
            $query = Reservation::with(['branch', 'organization', 'steward', 'tables']);

            // Apply admin-specific filters
            if (!$admin->is_super_admin) {
                if ($admin->branch_id) {
                    $query->where('branch_id', $admin->branch_id);
                } elseif ($admin->organization_id) {
                    $query->where('organization_id', $admin->organization_id);
                } else {
                    return view('admin.reservations.index', [
                        'reservations' => collect()->paginate(20),
                        'branches' => collect(),
                        'stewards' => collect(),
                        'filters' => []
                    ]);
                }
            }

            // Date filter setup
            if (!$startDate && !$endDate) {
                $startDate = now()->startOfDay()->toDateString();
                $endDate = now()->addDays(30)->toDateString();
            }

            // Apply date filters
            if ($startDate) {
                $query->where('date', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('date', '<=', $endDate);
            }

            // Apply other filters
            if ($status) {
                $query->where('status', $status);
            }
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
            if ($phone) {
                $query->where('phone', 'like', '%' . $phone . '%');
            }

            // Order by date and time
            $query->orderBy('date', 'desc')->orderBy('start_time', 'desc');

            // Get paginated results
            $reservations = $query->paginate(20);

            // Get branch options based on admin type
            if ($admin->is_super_admin) {
                $branches = Branch::where('is_active', true)->get();
            } elseif ($admin->organization_id && !$admin->branch_id) {
                $branches = Branch::where('organization_id', $admin->organization_id)
                    ->where('is_active', true)
                    ->get();
            } else {
                $branches = Branch::where('id', $admin->branch_id)
                    ->where('is_active', true)
                    ->get();
            }

            // Get stewards for the current branch
            $stewards = Employee::when($admin->branch_id, function($query) use ($admin) {
                    return $query->where('branch_id', $admin->branch_id);
                })
                ->whereHas('roles', function($query) {
                    $query->where('name', 'steward');
                })
                ->where('is_active', true)
                ->get();

            // Prepare filters for view
            $filters = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'status' => $status,
                'branchId' => $branchId,
                'phone' => $phone,
            ];

            return view('admin.reservations.index', compact('reservations', 'branches', 'stewards', 'filters'));

        } catch (\Exception $e) {
            \Log::error('Error in reservations index: ' . $e->getMessage(), [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('errors.generic', [
                'errorTitle' => 'System Error',
                'errorCode' => '500',
                'errorHeading' => 'Internal Server Error',
                'errorMessage' => 'An error occurred while loading reservations. Please try again later.',
                'headerClass' => 'bg-gradient-danger',
                'errorIcon' => 'fas fa-exclamation-triangle',
                'mainIcon' => 'fas fa-exclamation-triangle',
                'iconBgClass' => 'bg-red-100',
                'iconColor' => 'text-red-500',
                'buttonClass' => 'bg-red-500 hover:bg-red-600'
            ], 500);
        }

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
        $query = Reservation::with(['branch', 'organization', 'steward', 'tables']);

        // Apply permissions filter
        if ($admin->is_super_admin) {
            // Super admin can see all reservations
        } elseif ($admin->branch_id) {
            $query->where('branch_id', $admin->branch_id);
        } elseif ($admin->organization_id) {
            $query->where('organization_id', $admin->organization_id);
        }

        // Apply filters
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
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
        $branches = Branch::where('is_active', true)->get();
        $stewards = Employee::whereHas('roles', function($query) {
                $query->where('name', 'steward');
            })
            ->where('is_active', true)
            ->get();
        // Branch selection logic for super admin and org admin without branch
        if ($admin->is_super_admin) {
            $branches = Branch::where('is_active', true)->get();
        } elseif ($admin->organization_id && !$admin->branch_id) {
            $branches = Branch::where('organization_id', $admin->organization_id)
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::where('id', $admin->branch_id)->where('is_active', true)->get();
        }

        $filters = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'branchId' => $branchId,
            'phone' => $phone,
        ];
        // If no branch assigned, show error page
        if (!$admin->branch_id) {
            return response()->view('errors.generic', [
                'errorTitle' => 'Permission Denied',
                'errorCode' => '403',
                'errorHeading' => 'Permission Denied',
                'errorMessage' => 'You do not have permission to view reservations. Please contact administrator.',
                'headerClass' => 'bg-gradient-warning',
                'errorIcon' => 'fas fa-ban',
                'mainIcon' => 'fas fa-ban',
                'iconBgClass' => 'bg-yellow-100',
                'iconColor' => 'text-yellow-500',
                'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]'
            ], 403);
        }
        return view('admin.reservations.index', compact('reservations', 'branches', 'stewards', 'filters'));
    }

    /**
     * Get searchable columns for reservations
     */
   protected function getSearchableColumns(): array
    {
        return ['name', 'phone', 'email'];
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
        try {
            $admin = auth('admin')->user();

            if (!$admin) {
                return redirect()->route('admin.login');
            }

            // Load necessary relationships
            $reservation->load(['branch', 'tables', 'steward', 'organization']);

            // Permission check based on admin type
            if (!$admin->is_super_admin) {
                if ($admin->branch_id) {
                    // Branch admin can only edit reservations from their branch
                    if ($reservation->branch_id !== $admin->branch_id) {
                        throw new \Exception('You can only edit reservations from your assigned branch.');
                    }
                } elseif ($admin->organization_id) {
                    // Organization admin can edit reservations from their organization
                    if ($reservation->organization_id !== $admin->organization_id) {
                        throw new \Exception('You can only edit reservations from your organization.');
                    }
                } else {
                    throw new \Exception('You do not have permission to edit reservations.');
                }
            }

            // Get tables for the reservation's branch
            $tables = Table::where('branch_id', $reservation->branch_id)
                ->where('is_active', true)
                ->get();

            // Get stewards for the branch
            $stewards = Employee::where('branch_id', $reservation->branch_id)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'steward');
                })
                ->where('is_active', true)
                ->get();

            // Get branches based on admin type
            if ($admin->is_super_admin) {
                $branches = Branch::where('is_active', true)->get();
            } elseif ($admin->organization_id) {
                $branches = Branch::where('organization_id', $admin->organization_id)
                    ->where('is_active', true)
                    ->get();
            } else {
                $branches = Branch::where('id', $admin->branch_id)
                    ->where('is_active', true)
                    ->get();
            }

            $assignedTableIds = $reservation->tables->pluck('id')->toArray();
            $availableTableIds = $tables->pluck('id')->toArray();

            return view('admin.reservations.edit', compact(
                'reservation',
                'tables',
                'stewards',
                'branches',
                'assignedTableIds',
                'availableTableIds'
            ));

        } catch (\Exception $e) {
            \Log::error('Error in reservation edit: ' . $e->getMessage(), [
                'admin_id' => $admin->id ?? null,
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->view('errors.generic', [
                'errorTitle' => $e->getMessage(),
                'errorCode' => '403',
                'errorHeading' => 'Access Denied',
                'errorMessage' => $e->getMessage(),
                'headerClass' => 'bg-gradient-warning',
                'errorIcon' => 'fas fa-ban',
                'mainIcon' => 'fas fa-ban',
                'iconBgClass' => 'bg-yellow-100',
                'iconColor' => 'text-yellow-500',
                'buttonClass' => 'bg-[#FF9800] hover:bg-[#e68a00]'
            ], 403);
        }
    }

public function update(Request $request, Reservation $reservation)
{
    $admin = auth('admin')->user();
    // Allow super admin to update any reservation
    if (!$admin->isSuperAdmin() && $reservation->branch_id !== $admin->branch_id) {
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

        // Determine validation rules based on admin type
        if ($admin->is_super_admin) {
            $rules = [
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
                'branch_id' => 'required|exists:branches,id',
                'organization_id' => 'required|exists:organizations,id'
            ];
        } elseif ($admin->organization_id && !$admin->branch_id) {
            $rules = [
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
                'branch_id' => 'required|exists:branches,id'
            ];
        } else {
            $rules = [
                'name' => 'nullable|string|max:255',
                'phone' => 'required|string|min:10|max:15',
                'email' => 'nullable|email|max:255',
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'number_of_people' => 'required|integer|min:1',
                'assigned_table_ids' => 'nullable|array',
                'assigned_table_ids.*' => 'exists:tables,id',
                'steward_id' => 'nullable|exists:employees,id'
            ];
        }

        $validated = $request->validate($rules);

        // Assign branch/org IDs based on admin type
        if ($admin->is_super_admin) {
            $branchId = $validated['branch_id'];
            $organizationId = $validated['organization_id'];
        } elseif ($admin->organization_id && !$admin->branch_id) {
            $branchId = $validated['branch_id'];
            $organizationId = $admin->organization_id;
        } else {
            $branchId = $admin->branch_id;
            $organizationId = $admin->organization_id;
        }

        // Validate branch exists and belongs to org
        $branch = \App\Models\Branch::findOrFail($branchId);
        if ($organizationId && $branch->organization_id != $organizationId) {
            abort(403, 'Branch does not belong to your organization.');
        }

        try {
            // Validate branch exists and is active
            if (!$branch || !$branch->is_active) {
                \Log::error('Reservation creation failed: Invalid branch', ['branch_id' => $branch?->id, 'admin_id' => $admin->id]);
                return back()->withErrors(['error' => 'Invalid branch assignment. Cannot create reservation.'])->withInput();
            }

            // Validate branch operating hours with null-safe operators
            $branchOpenTime = $branch?->opening_time ? \Carbon\Carbon::parse($branch->opening_time)->format('H:i') : '00:00';
            $branchCloseTime = $branch?->closing_time ? \Carbon\Carbon::parse($branch->closing_time)->format('H:i') : '23:59';

            if ($validated['start_time'] < $branchOpenTime || $validated['end_time'] > $branchCloseTime) {
                \Log::warning('Reservation time outside branch hours', [
                    'branch_id' => $branch?->id,
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'branchOpenTime' => $branchOpenTime,
                    'branchCloseTime' => $branchCloseTime,
                ]);
                return back()->withErrors(['time' => 'Reservation time must be within branch operating hours (' . $branchOpenTime . ' - ' . $branchCloseTime . ')'])->withInput();
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
                    \Log::info('Reservation creation failed: Not enough capacity', [
                        'branch_id' => $branch?->id,
                        'requested_people' => $validated['number_of_people'],
                        'availableCapacity' => $availableCapacity,
                        'reservedCapacity' => $reservedCapacity
                    ]);
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
                    \Log::info('Reservation creation failed: Table conflict', [
                        'branch_id' => $branch?->id,
                        'conflicting_tables' => $conflictingTables,
                        'requested_tables' => $validated['assigned_table_ids']
                    ]);
                    return back()->withErrors(['assigned_table_ids' => 'The following tables are already reserved for the selected time: ' . implode(', ', $conflictingTables)])->withInput();
                }
            }

            // Calculate fees with null-safe operators
            $reservationFee = $branch?->reservation_fee ?? 0;
            $cancellationFee = $branch?->cancellation_fee ?? 0;
            $branchId = $branch?->id;

            if (!$branchId) {
                \Log::error('Reservation creation failed: Branch ID missing', ['branch' => $branch, 'admin_id' => $admin->id]);
                return back()->withErrors(['error' => 'Invalid branch assignment. Cannot create reservation.'])->withInput();
            }

            // Generate reservation number
            $lastReservation = Reservation::latest('id')->first();
            $reservationNumber = 'RES' . str_pad(($lastReservation ? $lastReservation->id + 1 : 1), 4, '0', STR_PAD_LEFT);

            $reservation = Reservation::create([
                'name' => $validated['name'] ?: $reservationNumber,
                'phone' => $validated['phone'] ?: $branch?->phone,
                'email' => $validated['email'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'number_of_people' => $validated['number_of_people'],
                'status' => 'pending',
                'branch_id' => $branchId,
                'organization_id' => $organizationId,
                'reservation_fee' => $reservationFee,
                'cancellation_fee' => $cancellationFee,
                'steward_id' => $validated['steward_id'] ?? null,
                'created_by_admin_id' => $admin->id,
                'type' => ReservationType::IN_CALL,
                'reservation_number' => $reservationNumber,
            ]);

            \Log::info('Reservation created', [
                'reservation_id' => $reservation->id,
                'admin_id' => $admin->id,
                'branch_id' => $branchId,
                'data' => $validated
            ]);

            if (!empty($validated['assigned_table_ids'])) {
                $reservation->tables()->sync($validated['assigned_table_ids']);
            }

            // Redirect to edit page with steward assignment and check-in options
            return redirect()->route('admin.reservations.edit', $reservation)
                ->with('success', 'Reservation ' . $reservationNumber . ' created successfully. You can now assign a steward and check in the guest.');
        }
        catch (\Exception $e) {
            \Log::error('Reservation creation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => $admin->id,
                'request_data' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Reservation creation failed: ' . $e->getMessage()])->withInput();
        }
    }

public function create(Request $request)
{
    $admin = auth('admin')->user();
    $data = [];

    if ($admin->is_super_admin) {
        // Super admin can select both organization and branch
        $data['organizations'] = Organization::where('is_active', true)->get();
        $data['branches'] = collect(); // Will be populated via AJAX
        $data['isSelectingOrg'] = true;
    }
    elseif ($admin->organization_id && !$admin->branch_id) {
        // Organization admin can only select branch
        $data['branches'] = Branch::where('organization_id', $admin->organization_id)
            ->where('is_active', true)
            ->get();
        $data['organization'] = Organization::find($admin->organization_id);
        $data['isSelectingBranch'] = true;
    }
    else {
        // Branch admin has everything pre-assigned
        $data['branch'] = Branch::find($admin->branch_id);
        $data['organization'] = Organization::find($admin->organization_id);
        $data['tables'] = Table::where('branch_id', $admin->branch_id)
            ->where('is_active', true)
            ->get();
        $data['stewards'] = Employee::where('branch_id', $admin->branch_id)
            ->where('position', 'steward')
            ->where('is_active', true)
            ->get();
    }

    // Get next reservation number
    $lastReservation = Reservation::latest('id')->first();
    $nextReservationNumber = 'RES' . str_pad(($lastReservation ? $lastReservation->id + 1 : 1), 4, '0', STR_PAD_LEFT);
    $data['nextReservationNumber'] = $nextReservationNumber;

    return view('admin.reservations.create', $data);
}


public function assignSteward(Request $request, Reservation $reservation)
{
    try {
        $validated = $request->validate([
            'steward_id' => 'required|exists:employees,id',
        ]);

        $reservation->update(['steward_id' => $validated['steward_id']]);
        $employee =Employee::find($validated['steward_id']);

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
    DB::beginTransaction();
    try {
        // Verify reservation exists and isn't already checked in
        if (!$reservation->exists) {
            throw new \Exception('Reservation not found');
        }

        if ($reservation->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation already checked in'
            ], 400);
        }

        // Check if reservation is for today
        if ($reservation->date != now()->toDateString()) {
            return response()->json([
                'success' => false,
                'message' => 'Can only check in reservations for today'
            ], 400);
        }

        // Check if within acceptable time range (e.g., 30 mins before start time)
        $startTime = \Carbon\Carbon::parse($reservation->date . ' ' . $reservation->start_time);
        $now = now();
        if ($now->diffInMinutes($startTime, false) > 30) {
            return response()->json([
                'success' => false,
                'message' => 'Too early to check in. Check-in opens 30 minutes before reservation time.'
            ], 400);
        }

        // Update check-in time
        $reservation->update([
            'check_in_time' => $now,
            'status' => 'checked_in'
        ]);

        // If steward is assigned, update their status
        if ($reservation->steward_id) {
            $reservation->steward()->update([
                'current_status' => 'serving'
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Reservation checked in successfully',
            'data' => [
                'check_in_time' => $reservation->fresh()->check_in_time->format('Y-m-d H:i:s'),
                'reservation_number' => $reservation->reservation_number,
                'steward_name' => $reservation->steward ? $reservation->steward->name : null,
                'table_numbers' => $reservation->tables->pluck('number')->join(', ')
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Check-in failed: ' . $e->getMessage(), [
            'reservation_id' => $reservation->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
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

    DB::beginTransaction();
    try {
        // Update checkout time
        $reservation->update(['check_out_time' => now()]);

        // Calculate total bill from orders
        $orders = $reservation->orders()
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalBill = 0;
        foreach ($orders as $order) {
            $totalBill += $order->total_amount;
        }

        // Add reservation fee if applicable
        if ($reservation->reservation_fee > 0) {
            $totalBill += $reservation->reservation_fee;
        }

        // Create or update bill record
        $bill = $reservation->bill()->updateOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'total_amount' => $totalBill,
                'status' => 'pending',
                'orders_total' => $orders->sum('total_amount'),
                'reservation_fee' => $reservation->reservation_fee,
                'generated_at' => now(),
            ]
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Reservation checked out successfully',
            'check_out_time' => $reservation->fresh()->check_out_time->format('Y-m-d H:i:s'),
            'bill_details' => [
                'total_amount' => $totalBill,
                'orders_count' => $orders->count(),
                'bill_id' => $bill->id,
                'bill_status' => $bill->status
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Checkout failed: ' . $e->getMessage(), [
            'reservation_id' => $reservation->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to check out: ' . $e->getMessage()
        ], 500);
    }
}

public function checkTableAvailability(Request $request)
{
    $request->validate([
        'date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'branch_id' => 'sometimes|exists:branches,id',
    ]);

    $admin = auth('admin')->user();
    $branchId = null;

    // Super admin: branch_id must be provided
    if ($admin->isSuperAdmin()) {
        $branchId = $request->input('branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required for super admin'], 400);
        }
    } elseif ($admin->organization_id && !$admin->branch_id) {
        // Org admin: branch_id must be provided
        $branchId = $request->input('branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required for organization admin'], 400);
        }
    } else {
        // Branch admin: use assigned branch
        $branchId = $admin->branch_id;
        if (!$branchId) {
            return response()->json(['error' => 'No branch assigned to admin'], 400);
        }
    }

    $conflictingReservations = Reservation::where('branch_id', $branchId)
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

    $allTableIds = Table::where('branch_id', $branchId)->where('is_active', true)->pluck('id');
    $reservedTableIds = $conflictingReservations->flatMap(function ($reservation) {
        return $reservation->tables->pluck('id');
    })->unique();
    $availableTableIds = $allTableIds->diff($reservedTableIds)->values();

    return response()->json([
        'available_table_ids' => $availableTableIds,
        'total_tables' => $allTableIds->count(),
        'available_count' => $availableTableIds->count(),
    ]);
}
// Add this method if not present
public function getBranchesByOrganization($organizationId)
{
    try {
        $branches = \App\Models\Branch::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->select('id', 'name', 'phone', 'address')
            ->orderBy('name')
            ->get();

        return response()->json($branches);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to fetch branches'], 500);
    }
}


public function createOrder(Reservation $reservation)
{

    // Use the correct route name for order creation from reservation
    return redirect()->route('admin.orders.create', ['reservation' => $reservation->id]);
}

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


                fputcsv($file, [
                    'ID', 'Name', 'Phone', 'Email', 'Date', 'Start Time', 'End Time',
                'ID', 'Name', 'Phone', 'Email', 'Date', 'Start Time', 'End Time',
                'Number of People', 'Status', 'Branch', 'Steward', 'Tables',
                'Reservation Fee', 'Cancellation Fee', 'Created At'
            ]);


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

    public function destroy($id)
    {

        return redirect()->back()->with('success', 'Deleted successfully');
    }

}
