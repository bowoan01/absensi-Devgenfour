<script>
$(function() {
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    let currentId = null;

    $('#report-filter').on('submit', function(e) {
        e.preventDefault();
        $.get(window.location.pathname === '/reports/table' ? '/reports' : window.location.pathname, $(this).serialize(), function(resp) {
            $('#reports-table').html(resp.html);
            flashElement('#reports-table table tbody tr:first-child');
        });
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
                $('#report-filter').trigger('submit');
            },
            error: function(xhr) {
                $('#attendance-errors').text(xhr.responseJSON?.message || 'Perubahan tidak dapat disimpan.');
            }
        });
    });
});
</script>
