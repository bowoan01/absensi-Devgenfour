@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Laporan {{ $student->full_name }}</h4>
        <small class="text-muted">ID {{ $student->student_id_code }} | {{ $student->department }}</small>
    </div>
    <div>
        <a href="{{ route('reports.export', $student) }}?{{ http_build_query($filters) }}" class="btn btn-outline-success btn-sm btn-raise">Ekspor CSV</a>
        <a href="{{ route('reports.export', $student) }}?format=xlsx&{{ http_build_query($filters) }}" class="btn btn-success btn-sm btn-raise">Ekspor XLSX</a>
    </div>
</div>
@include('admin.reports.partials.filter', ['students' => $students, 'filters' => $filters])
<div id="reports-table">
    @include('partials.reports.table', ['records' => $records, 'summary' => $summary])
</div>
@include('admin.reports.partials.edit-modal')
@endsection

@push('scripts')
@include('admin.reports.partials.scripts')
@endpush
