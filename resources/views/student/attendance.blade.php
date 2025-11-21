@extends('layouts.app')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">Welcome, {{ $student->full_name }}</h5>
                        <p class="text-muted mb-0">Today: {{ now()->toDayDateTimeString() }}</p>
                    </div>
                    <span class="badge bg-primary">ID: {{ $student->student_id_code }}</span>
                </div>
                <div class="alert {{ $todayAttendance?->status === 'late' ? 'alert-warning' : 'alert-success' }}">
                    {{ $todayAttendance ? 'Status: '.ucfirst($todayAttendance->status) : 'No attendance recorded yet today.' }}
                </div>
                <div class="mb-3">
                    <label class="form-label">Note (optional)</label>
                    <textarea id="attendanceNote" class="form-control" rows="2">{{ $todayAttendance->note ?? '' }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" id="checkInBtn" {{ $todayAttendance && $todayAttendance->isCheckedIn() ? 'disabled' : '' }}>Check In</button>
                    <button class="btn btn-outline-secondary" id="checkOutBtn" {{ !$todayAttendance || $todayAttendance->isCheckedOut() ? 'disabled' : '' }}>Check Out</button>
                </div>
                <div class="mt-3">
                    <p class="mb-1">Last action: {{ $todayAttendance?->check_out_at?->toDayDateTimeString() ?? $todayAttendance?->check_in_at?->toDayDateTimeString() ?? 'None' }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6>Attendance History</h6>
                <div id="historyTable">
                    @include('partials.attendance.history', ['history' => $history])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
