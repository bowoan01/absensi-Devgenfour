@extends('layouts.app')

@section('content')
<div class="row g-3 mb-3 align-items-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-0">Absensi Hari Ini</h4>
        <small class="text-muted">{{ $today->locale('id')->translatedFormat('l, d F Y') }}</small>
    </div>
    <div class="col-md-4 text-md-end">
        <div class="text-muted small">Waktu saat ini (WIB)</div>
        <div class="fw-bold" id="clock"></div>
    </div>
</div>
<div class="card shadow-sm border-0 mb-3 card-hover">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="fw-bold">Status:</div>
                <span class="badge bg-{{ optional($todayRecord)->status === 'late' ? 'warning text-dark' : (optional($todayRecord)->status === 'absent' ? 'secondary' : 'success') }}">
                    @php
                        $statusLabels = ['late' => 'Terlambat', 'absent' => 'Tidak Hadir', 'present' => 'Hadir'];
                    @endphp
                    {{ optional($todayRecord)->status ? ($statusLabels[$todayRecord->status] ?? 'Tidak diketahui') : 'Belum check-in' }}
                </span>
                @php
                    $lastAction = $todayRecord ? optional($todayRecord->check_out_at ?? $todayRecord->check_in_at)->timezone(config('app.timezone')) : null;
                @endphp
                @if($lastAction)
                    <div class="small text-muted">Aksi terakhir {{ $lastAction->format('H.i') }} WIB</div>
                @endif
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-raise" id="checkin-btn" @if($todayRecord && $todayRecord->check_in_at) disabled @endif>Check-in/Masuk</button>
                <button class="btn btn-outline-primary btn-raise" id="checkout-btn" @if(!$todayRecord || !$todayRecord->check_in_at || $todayRecord->check_out_at) disabled @endif>Check-out/Pulang</button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Catatan (opsional)</label>
            <textarea class="form-control" id="attendance-note" rows="2" placeholder="Tambahkan keterangan singkat bila perlu"></textarea>
        </div>
    </div>
</div>
<h5 class="fw-bold mb-2">Absensi Terbaru</h5>
<div class="card shadow-sm border-0 card-hover mb-3">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>
<h5 class="fw-bold mb-2">Riwayat Absensi</h5>
<div id="attendance-history">
    @include('partials.attendance.history', ['history' => $history])
</div>
@endsection

@push('scripts')
<script>
$(function() {
    startWibClock('#clock', { showSeconds: true });

    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            events: "{{ route('attendance.events') }}",
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            eventClassNames: function(arg) {
                return arg.event.extendedProps.status ? ['fc-status-' + arg.event.extendedProps.status] : [];
            },
            eventDidMount: function(info) {
                const props = info.event.extendedProps;
                const detail = `
                    <div><strong>${props.status_label || info.event.title}</strong></div>
                    <div>Masuk: ${props.jam_masuk || '-'}</div>
                    <div>Pulang: ${props.jam_pulang || '-'}</div>
                `;
                new bootstrap.Tooltip(info.el, {
                    title: detail,
                    html: true,
                    trigger: 'hover',
                    container: 'body',
                });
            }
        });
        calendar.render();
    }

    function handleAction(url) {
        $.post(url, { note: $('#attendance-note').val(), _token: $('meta[name="csrf-token"]').attr('content') })
            .done(function(resp) {
                showToast(resp.message, 'success');
                $('#attendance-history').html(resp.history);
                flashElement('#attendance-history .card:first');
                const today = resp.today;
                if (today.check_in_at) { $('#checkin-btn').prop('disabled', true); }
                if (today.check_out_at) { $('#checkout-btn').prop('disabled', true); }
            })
            .fail(function(xhr) {
                showToast(xhr.responseJSON?.message || 'Aksi tidak dapat diproses.', 'danger');
            });
    }

    $('#checkin-btn').on('click', function() { handleAction('/attendance/checkin'); });
    $('#checkout-btn').on('click', function() { handleAction('/attendance/checkout'); });
});
</script>
<style>
#calendar .fc-status-present {
    background-color: #198754;
    border-color: #198754;
    color: #fff;
}
#calendar .fc-status-late {
    background-color: #ffc107;
    border-color: #e0a800;
    color: #212529;
}
#calendar .fc-status-absent {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #fff;
}
#calendar .fc-event {
    cursor: pointer;
}
</style>
@endpush
