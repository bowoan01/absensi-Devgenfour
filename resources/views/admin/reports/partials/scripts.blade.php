<script>
$(function() {
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    let currentId = null;

    const table = $('#attendance-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: "{{ route('reports.datatable') }}",
            data: function(d) {
                d.student_id = $('#report-filter [name="student_id"]').val();
                d.start_date = $('#report-filter [name="start_date"]').val();
                d.end_date = $('#report-filter [name="end_date"]').val();
                d.status = $('#report-filter [name="status"]').val();
            }
        },
        columns: [
            { data: 'tanggal', name: 'attendance.date' },
            { data: 'nama_mahasiswa', name: 'students.full_name' },
            { data: 'check_in', name: 'attendance.check_in_at' },
            { data: 'check_out', name: 'attendance.check_out_at' },
            { data: 'status_label', name: 'attendance.status', orderable: false, searchable: false },
            { data: 'catatan', name: 'attendance.note', orderable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-end' },
        ],
        order: [[0, 'desc']],
        language: {
            processing: "Memuat...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data tersedia",
            infoFiltered: "(disaring dari total _MAX_ data)",
            zeroRecords: "Tidak ditemukan data yang cocok",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            },
        }
    });

    table.on('xhr', function(e, settings, json) {
        if (json && json.summary) {
            $('#summary-present').text(json.summary.present ?? 0);
            $('#summary-late').text(json.summary.late ?? 0);
            $('#summary-absent').text(json.summary.absent ?? 0);
        }
    });

    $('#report-filter').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    $(document).on('click', '.edit-attendance', function() {
        currentId = $(this).data('id');
        $('#attendance-errors').text('');
        $('#attendance-form')[0].reset();
        $('#attendance-form [name="check_in_at"]').val($(this).data('checkin') ? $(this).data('checkin').replace(' ', 'T') : '');
        $('#attendance-form [name="check_out_at"]').val($(this).data('checkout') ? $(this).data('checkout').replace(' ', 'T') : '');
        $('#attendance-form [name="status"]').val($(this).data('status'));
        $('#attendance-form [name="note"]').val($(this).data('note'));
        modal.show();
    });

    $('#save-attendance').on('click', function() {
        if (!currentId) return;
        $.ajax({
            url: '/reports/attendance/' + currentId,
            type: 'PUT',
            data: $('#attendance-form').serialize(),
            success: function(resp) {
                showToast(resp.message, 'success');
                modal.hide();
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                $('#attendance-errors').text(xhr.responseJSON?.message || 'Perubahan tidak dapat disimpan.');
            }
        });
    });
});
</script>
