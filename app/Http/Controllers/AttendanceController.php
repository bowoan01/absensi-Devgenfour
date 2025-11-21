<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        $today = Carbon::today();
        $todayRecord = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        $history = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->paginate(10);

        return view('student.attendance', compact('student', 'today', 'todayRecord', 'history'));
    }

    public function checkin(Request $request)
    {
        $student = $request->user()->student;
        $this->ensureActiveStudent($student);

        $request->validate([
            'note' => 'nullable|string|max:500',
        ], $this->noteMessages(), ['note' => 'catatan']);

        $today = Carbon::today();
        $existing = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing && $existing->check_in_at) {
            return response()->json(['message' => 'Anda sudah check-in hari ini.'], 422);
        }

        $now = Carbon::now();
        $record = $existing ?? new Attendance([
            'student_id' => $student->id,
            'date' => $today,
        ]);

        $record->check_in_at = $now;
        $record->status = Attendance::determineStatus($now);
        $record->note = $request->note;
        $record->save();

        Log::info('Student checked in', ['student' => $student->id, 'user' => $request->user()->id]);

        $history = Attendance::where('student_id', $student->id)->orderByDesc('date')->paginate(10);
        $todayRecord = $record;

        return response()->json([
            'message' => 'Check-in berhasil dicatat.',
            'today' => $todayRecord->fresh(),
            'history' => view('partials.attendance.history', compact('history'))->render(),
        ]);
    }

    public function checkout(Request $request)
    {
        $student = $request->user()->student;
        $this->ensureActiveStudent($student);

        $request->validate([
            'note' => 'nullable|string|max:500',
        ], $this->noteMessages(), ['note' => 'catatan']);

        $today = Carbon::today();
        $record = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if (! $record || ! $record->check_in_at) {
            return response()->json(['message' => 'Silakan lakukan check-in sebelum check-out.'], 422);
        }

        if ($record->check_out_at) {
            return response()->json(['message' => 'Anda sudah check-out hari ini.'], 422);
        }

        $record->check_out_at = Carbon::now();
        if ($request->filled('note')) {
            $record->note = $request->note;
        }
        $record->save();

        Log::info('Student checked out', ['student' => $student->id, 'user' => $request->user()->id]);

        $history = Attendance::where('student_id', $student->id)->orderByDesc('date')->paginate(10);

        return response()->json([
            'message' => 'Check-out berhasil dicatat.',
            'today' => $record->fresh(),
            'history' => view('partials.attendance.history', compact('history'))->render(),
        ]);
    }

    protected function ensureActiveStudent($student): void
    {
        if (! $student || $student->status !== 'active') {
            abort(403, 'Mahasiswa dengan status tidak aktif tidak dapat melakukan absensi.');
        }
    }

    protected function noteMessages(): array
    {
        return [
            'note.max' => 'Catatan maksimal :max karakter.',
            'note.string' => 'Catatan harus berupa teks.',
        ];
    }
}
