<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::orderBy('full_name')->get();
        $filters = $this->extractFilters($request);

        return view('admin.reports.index', [
            'students' => $students,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Student $student)
    {
        $students = Student::orderBy('full_name')->get();
        $filters = $this->extractFilters($request);
        $filters['student_id'] = $student->id;

        return view('admin.reports.index', [
            'students' => $students,
            'filters' => $filters,
            'selectedStudent' => $student,
        ]);
    }

    public function export(Request $request, Student $student)
    {
        $filters = $this->extractFilters($request);
        $filters['student_id'] = $student->id;
        $fileType = $request->get('format', 'csv');

        $response = new StreamedResponse(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Check-in (WIB)', 'Check-out (WIB)', 'Status', 'Catatan']);
            $statusLabels = ['late' => 'Terlambat', 'absent' => 'Tidak Hadir', 'present' => 'Hadir'];

            $records = $this->queryAttendance($filters, false)[0];
            foreach ($records as $record) {
                $checkIn = $record->check_in_at ? $record->check_in_at->timezone(config('app.timezone'))->format('H.i') : null;
                $checkOut = $record->check_out_at ? $record->check_out_at->timezone(config('app.timezone'))->format('H.i') : null;
                fputcsv($handle, [
                    $record->date->locale('id')->translatedFormat('d F Y'),
                    $checkIn,
                    $checkOut,
                    $statusLabels[$record->status] ?? ucfirst($record->status),
                    $record->note,
                ]);
            }
            fclose($handle);
        });

        $ext = $fileType === 'xlsx' ? 'xlsx' : 'csv';
        $response->headers->set('Content-Type', $fileType === 'xlsx' ? 'application/vnd.ms-excel' : 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="attendance_' . $student->id . '.' . $ext . '"');

        // Log::info('Report exported', ['user' => auth()->id(), 'student' => $student->id]);

        return $response;
    }

    public function datatable(Request $request)
    {
        $filters = $this->extractFilters($request);

        $baseQuery = Attendance::query()
            ->select('attendance.*')
            ->with('student')
            ->leftJoin('students', 'students.id', '=', 'attendance.student_id')
            ->when($filters['student_id'], fn($q) => $q->where('attendance.student_id', $filters['student_id']))
            ->forDateRange($filters['start_date'], $filters['end_date'])
            ->when($filters['status'], fn($q) => $q->where('attendance.status', $filters['status']))
            ->orderByDesc('attendance.date');

        $summaryQuery = clone $baseQuery;
        $summary = [
            'present' => (clone $summaryQuery)->where('attendance.status', Attendance::STATUS_PRESENT)->count(),
            'late' => (clone $summaryQuery)->where('attendance.status', Attendance::STATUS_LATE)->count(),
            'absent' => (clone $summaryQuery)->where('attendance.status', Attendance::STATUS_ABSENT)->count(),
        ];

        $statusLabels = [
            Attendance::STATUS_PRESENT => 'Hadir',
            Attendance::STATUS_LATE => 'Terlambat',
            Attendance::STATUS_ABSENT => 'Tidak Hadir',
        ];

        return DataTables::eloquent($baseQuery)
            ->addColumn('nama_mahasiswa', fn(Attendance $record) => optional($record->student)->full_name ?? '-')
            ->addColumn('tanggal', function (Attendance $record) {
                return $record->date ? $record->date->locale('id')->translatedFormat('d F Y') : '-';
            })
            ->addColumn('check_in', function (Attendance $record) {
                $checkIn = $record->check_in_at ? $record->check_in_at->timezone(config('app.timezone')) : null;
                return $checkIn ? $checkIn->format('H.i') . ' WIB' : '—';
            })
            ->addColumn('check_out', function (Attendance $record) {
                $checkOut = $record->check_out_at ? $record->check_out_at->timezone(config('app.timezone')) : null;
                return $checkOut ? $checkOut->format('H.i') . ' WIB' : '—';
            })
            ->addColumn('status_label', function (Attendance $record) use ($statusLabels) {
                $badge = $record->status === Attendance::STATUS_LATE ? 'warning text-dark' : ($record->status === Attendance::STATUS_ABSENT ? 'secondary' : 'success');
                $label = $statusLabels[$record->status] ?? 'Tidak diketahui';
                return '<span class="badge bg-' . $badge . '">' . $label . '</span>';
            })
            ->addColumn('catatan', fn(Attendance $record) => e($record->note ?: '—'))
            ->addColumn('aksi', function (Attendance $record) {
                $checkIn = $record->check_in_at ? $record->check_in_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i') : '';
                $checkOut = $record->check_out_at ? $record->check_out_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i') : '';
                $note = e($record->note ?? '');

                return '<button type="button" class="btn btn-outline-secondary btn-sm btn-raise edit-attendance" data-id="' . $record->id . '" data-checkin="' . $checkIn . '" data-checkout="' . $checkOut . '" data-status="' . $record->status . '" data-note="' . $note . '">Ubah</button>';
            })
            ->filterColumn('nama_mahasiswa', function ($query, $keyword) {
                $query->where('students.full_name', 'like', '%' . $keyword . '%');
            })
            ->orderColumn('nama_mahasiswa', 'students.full_name $1')
            ->rawColumns(['status_label', 'aksi'])
            ->with('summary', $summary)
            ->toJson();
    }

    public function updateAttendance(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'check_in_at' => 'nullable|date',
            'check_out_at' => 'nullable|date|after_or_equal:check_in_at',
            'status' => 'required|in:present,absent,late',
            'note' => 'nullable|string|max:500',
        ], $this->validationMessages(), $this->attributeLabels());

        $attendance->update($data);

        // Log::info('Attendance corrected', [
        //     'user' => auth()->id(),
        //     'attendance' => $attendance->id,
        //     'payload' => $data,
        // ]);

        return response()->json([
            'message' => 'Data absensi diperbarui.',
        ]);
    }

    protected function extractFilters(Request $request): array
    {
        return [
            'student_id' => $request->integer('student_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => $request->input('status'),
        ];
    }

    protected function queryAttendance(array $filters, bool $paginate = true): array
    {
        $query = Attendance::with('student')
            ->when($filters['student_id'], fn($q) => $q->where('student_id', $filters['student_id']))
            ->forDateRange($filters['start_date'], $filters['end_date'])
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->orderByDesc('date');

        $recordsQuery = clone $query;
        $records = $paginate ? $recordsQuery->paginate(10) : $recordsQuery->get();

        $summaryBase = clone $query;
        $summary = [
            'present' => (clone $summaryBase)->where('status', 'present')->count(),
            'late' => (clone $summaryBase)->where('status', 'late')->count(),
            'absent' => (clone $summaryBase)->where('status', 'absent')->count(),
        ];

        return [$records, $summary];
    }

    protected function validationMessages(): array
    {
        return [
            'date' => ':attribute harus berupa tanggal dan waktu yang valid.',
            'after_or_equal' => ':attribute harus sama atau setelah waktu masuk.',
            'in' => 'Pilihan :attribute tidak valid.',
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }

    protected function attributeLabels(): array
    {
        return [
            'check_in_at' => 'Waktu check-in',
            'check_out_at' => 'Waktu check-out',
            'status' => 'Status',
            'note' => 'Catatan',
        ];
    }
}
