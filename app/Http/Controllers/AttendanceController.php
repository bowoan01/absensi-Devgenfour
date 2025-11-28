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

    public function events(Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            abort(403, 'Mahasiswa tidak ditemukan.');
        }

        $records = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(120)
            ->get();

        $statusLabels = [
            Attendance::STATUS_PRESENT => 'Hadir',
            Attendance::STATUS_LATE => 'Terlambat',
            Attendance::STATUS_ABSENT => 'Tidak Hadir',
        ];

        $events = $records->map(function (Attendance $record) use ($statusLabels) {
            $checkIn = $record->check_in_at ? $record->check_in_at->timezone(config('app.timezone')) : null;
            $checkOut = $record->check_out_at ? $record->check_out_at->timezone(config('app.timezone')) : null;

            $status = $record->status;
            $statusText = $statusLabels[$status] ?? ucfirst($status);

            $jamMasuk = $checkIn ? $checkIn->format('H.i') . ' WIB' : '-';
            $jamPulang = $checkOut ? $checkOut->format('H.i') . ' WIB' : '-';

            $title = $statusText;
            if ($status !== Attendance::STATUS_ABSENT) {
                $title .= ' (' . ($jamMasuk !== '-' ? $jamMasuk : '?') . ' - ' . ($jamPulang !== '-' ? $jamPulang : '?') . ')';
            }

            $start = $checkIn ? $checkIn->toIso8601String() : $record->date->toDateString();
            $end = $checkOut ? $checkOut->toIso8601String() : null;

            $classNames = match ($status) {
                Attendance::STATUS_LATE => ['fc-status-late'],
                Attendance::STATUS_ABSENT => ['fc-status-absent'],
                default => ['fc-status-present'],
            };

            return [
                'title' => $title,
                'start' => $start,
                'end' => $end,
                'allDay' => ! $checkIn,
                'classNames' => $classNames,
                'extendedProps' => [
                    'status' => $status,
                    'status_label' => $statusText,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                ],
            ];
        });

        return response()->json($events);
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
