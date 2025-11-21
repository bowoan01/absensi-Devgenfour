@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="mb-3">Attendance Reports</h4>
        <form id="reportFilters" class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select">
                    <option value="">All Students</option>
                    @foreach($students as $stu)
                        <option value="{{ $stu->id }}" @selected(request('student_id') == $stu->id)>{{ $stu->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="present" @selected(request('status')==='present')>Present</option>
                    <option value="late" @selected(request('status')==='late')>Late</option>
                    <option value="absent" @selected(request('status')==='absent')>Absent</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">Apply</button>
                <a href="{{ route('reports.index') }}" class="btn btn-link">Reset</a>
            </div>
        </form>
        <div class="d-flex gap-2 mb-3">
            <span class="badge bg-success">Present: {{ $totals['present'] }}</span>
            <span class="badge bg-warning text-dark">Late: {{ $totals['late'] }}</span>
            <span class="badge bg-danger">Absent: {{ $totals['absent'] }}</span>
        </div>
        @php $lastId = $attendance->getCollection()->max('id') ?? 0; @endphp
        <div id="reportTable" data-last-id="{{ $lastId }}">
            @include('partials.reports.table', ['attendance' => $attendance])
        </div>
    </div>
</div>
@endsection
