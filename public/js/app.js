$(document).ready(function() {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    function showToast(message, type = 'success') {
        const id = Date.now();
        const toast = $(`
            <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000" id="toast-${id}">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        $('#toast-container').append(toast);
        new bootstrap.Toast(toast[0]).show();
    }

    // Student management (only initialize when the modal exists on the page)
    const studentModalEl = document.getElementById('studentModal');
    const studentModal = studentModalEl ? bootstrap.Modal.getOrCreateInstance(studentModalEl) : null;

    if (studentModal) {
        $('#addStudentBtn').on('click', function() {
            $('#studentForm')[0].reset();
            $('#student_id').val('');
            studentModal.show();
        });

        $(document).on('click', '.edit-student', function() {
            const id = $(this).data('id');
            $.get(`/students/${id}/edit`, function(data) {
                $('#student_id').val(data.id);
                $('#studentForm [name="name"]').val(data.full_name);
                $('#studentForm [name="email"]').val(data.user.email);
                $('#studentForm [name="student_id_code"]').val(data.student_id_code);
                $('#studentForm [name="department"]').val(data.department);
                $('#studentForm [name="start_date"]').val(data.start_date);
                $('#studentForm [name="end_date"]').val(data.end_date);
                $('#studentForm [name="status"]').val(data.status);
                studentModal.show();
            });
        });

        $('#saveStudent').on('click', function() {
            const id = $('#student_id').val();
            const method = id ? 'PUT' : 'POST';
            const url = id ? `/students/${id}` : '/students';
            $.ajax({
                url: url,
                method: method,
                data: $('#studentForm').serialize(),
                success: function(resp) {
                    $('#studentsTable').html(resp.html);
                    studentModal.hide();
                    showToast(resp.message);
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Validation error', 'danger');
                }
            });
        });

        $(document).on('click', '.delete-student', function() {
            if (!confirm('Delete this student?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: `/students/${id}`,
                method: 'DELETE',
                success: function(resp) {
                    $('#studentsTable').html(resp.html);
                    showToast(resp.message);
                },
                error: () => showToast('Unable to delete student', 'danger')
            });
        });
    }

    $(document).on('click', '.toggle-status', function() {
        const id = $(this).data('id');
        $.ajax({
            url: `/students/${id}/status`,
            method: 'PATCH',
            success: function(resp) {
                $('#studentsTable').html(resp.html);
                showToast(resp.message);
            },
            error: () => showToast('Unable to update status', 'danger')
        });
    });

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        $.get('/students', $(this).serialize(), function(resp) {
            $('#studentsTable').html(resp.html);
        });
    });

    // Admin reports: poll for new attendance rows and prepend without full reload
    const $reportTable = $('#reportTable');
    if ($reportTable.length) {
        let lastId = Number($reportTable.data('last-id')) || 0;
        const $reportTbody = $('#reportTableBody');

        setInterval(function() {
            $.getJSON('/reports/latest', { after_id: lastId }, function(resp) {
                if (!resp.data || !resp.data.length) return;

                resp.data.forEach(function(item) {
                    lastId = item.id;
                    const badgeClass = item.status === 'late' ? 'warning text-dark' : (item.status === 'absent' ? 'danger' : 'success');
                    const statusLabel = item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : '';
                    const row = `
                        <tr data-id="${item.id}">
                            <td>${item.student || ''}</td>
                            <td>${item.date || ''}</td>
                            <td>${item.check_in_at || ''}</td>
                            <td>${item.check_out_at || ''}</td>
                            <td><span class="badge bg-${badgeClass}">${statusLabel}</span></td>
                            <td>${item.note || ''}</td>
                        </tr>
                    `;
                    $reportTbody.prepend(row);
                });
            });
        }, 5000);
    }

    // Attendance student actions
    $('#checkInBtn').on('click', function() {
        const note = $('#attendanceNote').val();
        const btn = $(this);
        btn.prop('disabled', true);
        $.post('/attendance/checkin', { note }, function(resp) {
            showToast(resp.message);
            $('#historyTable').html(resp.html);
            $('#checkOutBtn').prop('disabled', false);
        }).fail(function(xhr) {
            showToast(xhr.responseJSON?.message || 'Cannot check in', 'danger');
            btn.prop('disabled', false);
        });
    });

    $('#checkOutBtn').on('click', function() {
        const note = $('#attendanceNote').val();
        const btn = $(this);
        btn.prop('disabled', true);
        $.post('/attendance/checkout', { note }, function(resp) {
            showToast(resp.message);
            $('#historyTable').html(resp.html);
        }).fail(function(xhr) {
            showToast(xhr.responseJSON?.message || 'Cannot check out', 'danger');
            btn.prop('disabled', false);
        });
    });

    // Reports filtering
    $('#reportFilters').on('submit', function(e) {
        e.preventDefault();
        const params = $(this).serialize();
        const target = window.location.pathname.includes('/reports/') ? window.location.pathname : '/reports';
        $.get(target, params, function(resp) {
            $('#reportTable').html(resp.html);
            if (resp.totals) {
                $('.badge.bg-success').text('Present: ' + resp.totals.present);
                $('.badge.bg-warning').text('Late: ' + resp.totals.late);
                $('.badge.bg-danger').text('Absent: ' + resp.totals.absent);
            }
        });
    });
});
