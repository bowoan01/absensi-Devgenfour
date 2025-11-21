@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Laporan Absensi</h4>
        <small class="text-muted">Saring berdasarkan mahasiswa, rentang tanggal, dan status.</small>
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
