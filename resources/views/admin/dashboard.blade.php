@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold">Dasbor</h3>
    <p class="text-muted">Ringkasan {{ $today->locale('id')->translatedFormat('d F Y') }}</p>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 card-hover">
            <div class="card-body">
                <div class="text-muted">Hadir Hari Ini</div>
                <div class="display-6 fw-bold">{{ $stats['present'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 card-hover">
            <div class="card-body">
                <div class="text-muted">Belum Check-in</div>
                <div class="display-6 fw-bold text-warning">{{ $stats['missing'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 card-hover">
            <div class="card-body">
                <div class="text-muted">Terlambat</div>
                <div class="display-6 fw-bold text-danger">{{ $stats['late'] }}</div>
            </div>
        </div>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Absensi Terbaru</h5>
    <div>
        <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-primary btn-raise">Daftar Mahasiswa</a>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-primary btn-raise">Laporan</a>
    </div>
</div>
<div class="card shadow-sm border-0 card-hover">
    <div class="card-body p-0">
        <table class="table table-striped mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mahasiswa</th>
                    <th>Tanggal</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $record)
                    @php
                        $statusLabels = ['late' => 'Terlambat', 'absent' => 'Tidak Hadir', 'present' => 'Hadir'];
                        $checkIn = optional($record->check_in_at)->timezone(config('app.timezone'));
                        $checkOut = optional($record->check_out_at)->timezone(config('app.timezone'));
                    @endphp
                    <tr>
                        <td>{{ $record->student->full_name ?? 'N/A' }}</td>
                        <td>{{ $record->date->locale('id')->translatedFormat('d F Y') }}</td>
                        <td>{{ $checkIn ? $checkIn->format('H.i') . ' WIB' : '-' }}</td>
                        <td>{{ $checkOut ? $checkOut->format('H.i') . ' WIB' : '-' }}</td>
                        <td><span class="badge bg-{{ $record->status === 'late' ? 'warning text-dark' : ($record->status === 'absent' ? 'secondary' : 'success') }}">{{ $statusLabels[$record->status] ?? 'Tidak Diketahui' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
