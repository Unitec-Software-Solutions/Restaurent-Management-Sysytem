<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Models\StaffShift;
use App\Models\StaffAttendance;
use App\Models\StaffTrainingRecord;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function createStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:staff_profiles',
            'phone_number' => 'required|string|max:20|unique:staff_profiles',
            'branch_id' => 'required|exists:branches,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $staff = StaffProfile::create($request->all());

        // Assign role
        $staff->roles()->attach($request->role_id, [
            'branch_id' => $request->branch_id,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Staff created successfully',
            'staff' => $staff->load('roles'),
        ], 201);
    }

    public function assignShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'shift_id' => 'required|exists:shifts,id',
            'branch_id' => 'required|exists:branches,id',
            'date' => 'required|date',
            'is_training_mode' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shift = StaffShift::create($request->all());

        return response()->json([
            'message' => 'Shift assigned successfully',
            'shift' => $shift,
        ], 201);
    }

    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shift = StaffShift::where('staff_profile_id', $request->staff_profile_id)
            ->where('branch_id', $request->branch_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'No shift assigned for today'], 404);
        }

        if ($shift->clock_in) {
            return response()->json(['message' => 'Already clocked in'], 400);
        }

        $shift->update(['clock_in' => now()]);

        return response()->json([
            'message' => 'Clocked in successfully',
            'shift' => $shift->fresh(),
        ]);
    }

    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shift = StaffShift::where('staff_profile_id', $request->staff_profile_id)
            ->where('branch_id', $request->branch_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'No shift assigned for today'], 404);
        }

        if (!$shift->clock_in) {
            return response()->json(['message' => 'Not clocked in'], 400);
        }

        if ($shift->clock_out) {
            return response()->json(['message' => 'Already clocked out'], 400);
        }

        $shift->update(['clock_out' => now()]);

        return response()->json([
            'message' => 'Clocked out successfully',
            'shift' => $shift->fresh(),
        ]);
    }

    public function recordAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'branch_id' => 'required|exists:branches,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,half-day',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = StaffAttendance::create($request->all());

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'attendance' => $attendance,
        ], 201);
    }

    public function addTrainingRecord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'trainer_id' => 'required|exists:staff_profiles,id',
            'training_type' => 'required|string|max:255',
            'description' => 'required|string',
            'training_date' => 'required|date',
            'is_completed' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $training = StaffTrainingRecord::create($request->all());

        return response()->json([
            'message' => 'Training record added successfully',
            'training' => $training,
        ], 201);
    }

    public function getStaffShifts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shifts = StaffShift::where('staff_profile_id', $request->staff_profile_id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->with(['shift', 'branch'])
            ->get();

        return response()->json(['shifts' => $shifts]);
    }

    public function getStaffAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = StaffAttendance::where('staff_profile_id', $request->staff_profile_id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->with('branch')
            ->get();

        return response()->json(['attendance' => $attendance]);
    }
} 