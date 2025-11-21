<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isStudent()) {
            return redirect()->route('attendance.index');
        }

        $today = Carbon::today();

        $stats = Cache::remember("dashboard-stats-{$today->toDateString()}", 60, function () use ($today) {
            $checkIns = Attendance::whereDate('date', $today)->whereNotNull('check_in_at')->count();
            $late = Attendance::whereDate('date', $today)->where('status', Attendance::STATUS_LATE)->count();
            $activeStudents = Student::where('status', 'active')->count();
            $missing = max($activeStudents - $checkIns, 0);
            $recent = Attendance::with('student.user')
                ->whereDate('date', $today)
                ->orderByDesc('check_in_at')
                ->take(10)
                ->get();

            return compact('checkIns', 'late', 'missing', 'recent');
        });

        return view('admin.dashboard', [
            'checkIns' => $stats['checkIns'],
            'late' => $stats['late'],
            'missing' => $stats['missing'],
            'recent' => $stats['recent'],
        ]);
    }
}
