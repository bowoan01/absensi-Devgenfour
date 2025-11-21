<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $this->markAbsentForDay($today);

        $stats = Cache::remember('dashboard_stats_' . $today->toDateString(), 60, function () use ($today) {
            $todayAttendance = Attendance::whereDate('date', $today)->with('student')->get();
            $activeStudents = Student::active()->count();

            $presentCount = $todayAttendance->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])->count();
            $lateCount = $todayAttendance->where('status', Attendance::STATUS_LATE)->count();
            $missingCount = max($activeStudents - $presentCount, 0);

            return [
                'present' => $presentCount,
                'late' => $lateCount,
                'missing' => $missingCount,
            ];
        });

        $recent = Attendance::with('student')
            ->orderByDesc('date')
            ->orderByDesc('check_in_at')
            ->limit(10)
            ->get();

        Log::info('Dashboard viewed', ['user' => auth()->id()]);

        return view('admin.dashboard', compact('stats', 'recent', 'today'));
    }

    protected function markAbsentForDay(Carbon $date): void
    {
        if (Carbon::now()->lessThan($date->copy()->setTime(10, 0))) {
            return;
        }

        $students = Student::active()->pluck('id');
        foreach ($students as $studentId) {
            Attendance::firstOrCreate(
                ['student_id' => $studentId, 'date' => $date->toDateString()],
                ['status' => Attendance::STATUS_ABSENT]
            );
        }
    }
}
