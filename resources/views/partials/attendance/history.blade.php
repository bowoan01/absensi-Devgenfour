<div class="row g-3">
    @forelse($history as $record)
        <div class="col-12 col-md-6">
            <div class="card h-100 card-hover">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>{{ $record->date->locale('id')->translatedFormat('d F Y') }}</strong><br>
                            @php
                                $statusLabels = ['late' => 'Terlambat', 'absent' => 'Tidak Hadir', 'present' => 'Hadir'];
                                $checkIn = optional($record->check_in_at)->timezone(config('app.timezone'));
                                $checkOut = optional($record->check_out_at)->timezone(config('app.timezone'));
                            @endphp
                            <span class="badge bg-{{ $record->status === 'late' ? 'warning text-dark' : ($record->status === 'absent' ? 'secondary' : 'success') }}">{{ $statusLabels[$record->status] ?? 'Tidak diketahui' }}</span>
                        </div>
                        <div class="text-end small text-muted">
                            Masuk: {{ $checkIn ? $checkIn->format('H.i') . ' WIB' : '-' }}<br>
                            Pulang: {{ $checkOut ? $checkOut->format('H.i') . ' WIB' : '-' }}
                        </div>
                    </div>
                    <p class="mb-0 text-muted">{{ $record->note ?: 'Tidak ada catatan.' }}</p>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-4">Belum ada riwayat absensi.</div>
    @endforelse
</div>
@if(method_exists($history, 'links'))
    <div class="d-flex justify-content-end mt-3">{{ $history->links() }}</div>
@endif
