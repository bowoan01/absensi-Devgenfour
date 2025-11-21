<div class="row g-3 mb-3">
    <div class="col-auto"><span class="badge bg-success">Hadir: {{ $summary['present'] ?? 0 }}</span></div>
    <div class="col-auto"><span class="badge bg-warning text-dark">Terlambat: {{ $summary['late'] ?? 0 }}</span></div>
    <div class="col-auto"><span class="badge bg-secondary">Tidak Hadir: {{ $summary['absent'] ?? 0 }}</span></div>
</div>
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Tanggal</th>
            <th>Masuk</th>
            <th>Pulang</th>
            <th>Status</th>
            <th>Catatan</th>
            <th class="text-end">Aksi Admin</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $record)
            @php
                $statusLabels = ['late' => 'Terlambat', 'absent' => 'Tidak Hadir', 'present' => 'Hadir'];
                $checkIn = optional($record->check_in_at)->timezone(config('app.timezone'));
                $checkOut = optional($record->check_out_at)->timezone(config('app.timezone'));
            @endphp
            <tr>
                <td>{{ $record->date->locale('id')->translatedFormat('d F Y') }}</td>
                <td>{{ $checkIn ? $checkIn->format('H.i') . ' WIB' : '-' }}</td>
                <td>{{ $checkOut ? $checkOut->format('H.i') . ' WIB' : '-' }}</td>
                <td><span class="badge bg-{{ $record->status === 'late' ? 'warning text-dark' : ($record->status === 'absent' ? 'secondary' : 'success') }}">{{ $statusLabels[$record->status] ?? 'Tidak diketahui' }}</span></td>
                <td>{{ $record->note ?: '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-outline-secondary btn-sm btn-raise edit-attendance" data-id="{{ $record->id }}" data-checkin="{{ $record->check_in_at }}" data-checkout="{{ $record->check_out_at }}" data-status="{{ $record->status }}" data-note="{{ $record->note }}">Ubah</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data absensi.</td></tr>
        @endforelse
    </tbody>
</table>
@if(method_exists($records, 'links'))
    <div class="d-flex justify-content-end">{{ $records->links() }}</div>
@endif
