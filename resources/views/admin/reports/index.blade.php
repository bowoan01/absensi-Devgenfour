@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Laporan Absensi</h4>
        <small class="text-muted">Saring berdasarkan mahasiswa, rentang tanggal, dan status.</small>
    </div>
</div>
@include('admin.reports.partials.filter', ['students' => $students, 'filters' => $filters])
<div class="row g-2 mb-3" id="attendance-summary">
    <div class="col-auto"><span class="badge bg-success">Hadir: <span id="summary-present">0</span></span></div>
    <div class="col-auto"><span class="badge bg-warning text-dark">Terlambat: <span id="summary-late">0</span></span></div>
    <div class="col-auto"><span class="badge bg-secondary">Tidak Hadir: <span id="summary-absent">0</span></span></div>
</div>
<div class="card shadow-sm border-0 card-hover">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle w-100" id="attendance-table">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Mahasiswa</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th class="text-end">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@include('admin.reports.partials.edit-modal')
@endsection

@push('scripts')
@include('admin.reports.partials.scripts')
@endpush
