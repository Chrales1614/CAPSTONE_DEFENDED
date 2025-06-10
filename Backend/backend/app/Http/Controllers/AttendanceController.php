<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->paginate(10);
        return response()->json($attendance);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time_in' => 'required|date_format:H:i:s',
            'biometric_id' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::create($request->all());
        return response()->json($attendance, 201);
    }

    public function show(Attendance $attendance)
    {
        return response()->json($attendance->load('user'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'time_out' => 'required|date_format:H:i:s',
            'status' => 'required|in:present,late,absent',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance->update($request->all());
        return response()->json($attendance);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(null, 204);
    }

    public function getByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::with('user')
            ->whereDate('date', $request->date)
            ->get();
        return response()->json($attendance);
    }

    public function getByUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get();
        return response()->json($attendance);
    }

    public function recordBiometricAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'biometric_id' => 'required|string|max:100',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find user by biometric ID
        $user = User::where('biometric_id', $request->biometric_id)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $date = now()->format('Y-m-d');
        $time = now()->format('H:i:s');

        // Check if attendance record exists for today
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if ($attendance) {
            // Update time out
            $attendance->update([
                'time_out' => $time,
                'status' => $this->calculateStatus($attendance->time_in, $time)
            ]);
        } else {
            // Create new attendance record
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'time_in' => $time,
                'biometric_id' => $request->biometric_id,
                'status' => $this->calculateStatus($time, null)
            ]);
        }

        return response()->json($attendance);
    }

    private function calculateStatus($timeIn, $timeOut)
    {
        $startTime = '08:00:00';
        $lateTime = '08:30:00';

        if (!$timeIn) {
            return 'absent';
        }

        if ($timeIn > $lateTime) {
            return 'late';
        }

        if ($timeOut && $timeOut < '17:00:00') {
            return 'late';
        }

        return 'present';
    }
} 