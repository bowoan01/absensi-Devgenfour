@extends('layouts.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Report: {{ $student->full_name }}</h4>
                <p class="text-muted mb-0">{{ $student->department }} | {{ $student->student_id_code }}</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('reports.export', ['student' => $student, 'format' => 'csv'] + request()->all()) }}" class="btn btn-outline-primary">Export CSV</a>
                <a href="{{ route('reports.export', ['student' => $student, 'format' => 'xlsx'] + request()->all()) }}" class="btn btn-primary">Export XLSX</a>
            </div>
        </div>
        <form id="reportFilters" class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="present" @selected(request('status')==='present')>Present</option>
                    <option value="late" @selected(request('status')==='late')>Late</option>
                    <option value="absent" @selected(request('status')==='absent')>Absent</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary">Apply</button>
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
