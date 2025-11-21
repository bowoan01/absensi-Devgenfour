<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with('user')->orderBy('full_name')->get();
        $query = $this->buildQuery($request);
        $attendance = $query->paginate(10)->withQueryString();
        $totals = $this->totals(clone $query);

        if ($request->ajax()) {
            $table = view('partials.reports.table', compact('attendance'))->render();
            return response()->json(['html' => $table, 'totals' => $totals]);
        }

        return view('admin.reports.index', compact('students', 'attendance', 'totals'));
    }

    public function show(Student $student, Request $request)
    {
        $students = Student::with('user')->orderBy('full_name')->get();
        $query = $this->buildQuery($request, $student->id);
        $attendance = $query->paginate(10)->withQueryString();
        $totals = $this->totals(clone $query);

        if ($request->ajax()) {
            $table = view('partials.reports.table', compact('attendance'))->render();
            return response()->json(['html' => $table, 'totals' => $totals]);
        }

        return view('admin.reports.show', compact('students', 'attendance', 'student', 'totals'));
    }

    public function export(Student $student, Request $request)
    {
        $query = $this->buildQuery($request, $student->id);
        $format = $request->get('format', 'csv');
        $fileName = 'attendance-'.$student->student_id_code.'-'.now()->format('Ymd_His').'.'.$format;

        $export = new AttendanceExport($query->with('student.user'));
        $writerType = $format === 'xlsx' ? \Maatwebsite\Excel\Excel::XLSX : \Maatwebsite\Excel\Excel::CSV;

        return Excel::download($export, $fileName, $writerType);
    }

    public function table(Request $request)
    {
        $query = $this->buildQuery($request, $request->input('student_id'));
        $attendance = $query->paginate(10)->withQueryString();
        $table = view('partials.reports.table', compact('attendance'))->render();
        $totals = $this->totals(clone $query);

        return response()->json(['html' => $table, 'totals' => $totals]);
    }

    public function latest(Request $request)
    {
        $afterId = (int) $request->input('after_id', 0);
        $timezone = config('app.timezone');

        $data = Attendance::with('student')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->take(50)
            ->get()
            ->map(function ($item) use ($timezone) {
                return [
                    'id' => $item->id,
                    'student' => $item->student?->full_name,
                    'date' => optional($item->date)->setTimezone($timezone)->format('Y-m-d'),
                    'check_in_at' => optional($item->check_in_at?->setTimezone($timezone))->format('H:i'),
                    'check_out_at' => optional($item->check_out_at?->setTimezone($timezone))->format('H:i'),
                    'status' => $item->status,
                    'note' => $item->note,
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    protected function buildQuery(Request $request, int $studentId = null)
    {
        $query = Attendance::with('student.user')
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn($q) => $q->whereDate('date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn($q) => $q->whereDate('date', '<=', $request->input('to')))
            ->orderByDesc('date');

        return $query;
    }

    protected function totals($query): array
    {
        return [
            'present' => (clone $query)->where('status', Attendance::STATUS_PRESENT)->count(),
            'late' => (clone $query)->where('status', Attendance::STATUS_LATE)->count(),
            'absent' => (clone $query)->where('status', Attendance::STATUS_ABSENT)->count(),
        ];
    }
}
