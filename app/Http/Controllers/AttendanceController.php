<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 403);
        $today = Carbon::today();
        $todayAttendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        $history = Attendance::with('student')
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->paginate(10);

        if ($request->ajax()) {
            $html = view('partials.attendance.history', compact('history'))->render();
            return response()->json(['html' => $html]);
        }

        return view('student.attendance', compact('todayAttendance', 'history', 'student'));
    }

    public function checkIn(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 403);
        if ($student->status !== \App\Models\User::STATUS_ACTIVE) {
            return response()->json(['message' => 'Account inactive.'], 422);
        }

        $request->validate(['note' => 'nullable|string|max:500']);
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::firstOrNew([
            'student_id' => $student->id,
            'date' => $today,
        ]);

        if ($attendance->isCheckedIn()) {
            return response()->json(['message' => 'Already checked in for today.'], 422);
        }

        $attendance->fill([
            'check_in_at' => $now,
            'status' => Attendance::determineStatusForCheckIn($now),
            'note' => $request->input('note'),
        ]);
        $attendance->save();

        $history = Attendance::where('student_id', $student->id)->orderByDesc('date')->paginate(10);
        $html = view('partials.attendance.history', ['history' => $history])->render();

        return response()->json([
            'message' => 'Check-in recorded.',
            'status' => $attendance->status,
            'check_in_at' => $attendance->check_in_at->toDateTimeString(),
            'html' => $html,
        ]);
    }

    public function checkOut(Request $request)
    {
        $student = $request->user()->student;
        abort_unless($student, 403);
        if ($student->status !== \App\Models\User::STATUS_ACTIVE) {
            return response()->json(['message' => 'Account inactive.'], 422);
        }

        $request->validate(['note' => 'nullable|string|max:500']);
        $today = Carbon::today();

        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->isCheckedIn()) {
            return response()->json(['message' => 'You must check in first.'], 422);
        }

        if ($attendance->isCheckedOut()) {
            return response()->json(['message' => 'Already checked out.'], 422);
        }

        $attendance->check_out_at = Carbon::now();
        $attendance->note = $request->input('note', $attendance->note);
        $attendance->save();

        $history = Attendance::where('student_id', $student->id)->orderByDesc('date')->paginate(10);
        $html = view('partials.attendance.history', ['history' => $history])->render();

        return response()->json([
            'message' => 'Check-out recorded.',
            'check_out_at' => $attendance->check_out_at->toDateTimeString(),
            'html' => $html,
        ]);
    }
}
