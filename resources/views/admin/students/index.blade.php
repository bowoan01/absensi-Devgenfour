@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">Daftar Mahasiswa Magang</h4>
        <small class="text-muted">Kelola data peserta, status akun, dan laporan.</small>
    </div>
    <button class="btn btn-primary btn-raise" id="add-student-btn"><i class="bi bi-plus"></i> Tambah Mahasiswa</button>
</div>
<div class="card shadow-sm border-0 mb-3 card-hover">
    <div class="card-body">
        <form id="filter-form" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, NIM, atau jurusan">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="department" class="form-control" placeholder="Departemen/Jurusan">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary btn-raise" type="submit">Terapkan</button>
            </div>
        </form>
    </div>
</div>
<div class="card shadow-sm border-0 card-hover">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle w-100" id="students-table">
                <thead class="table-light">
                    <tr>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Jurusan</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="studentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="student-form">
                    @csrf
                    <input type="hidden" id="student-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="Nama sesuai identitas">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="nama@email.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username (opsional)</label>
                            <input type="text" name="username" class="form-control" placeholder="Gunakan jika ingin login dengan username">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NIM</label>
                            <input type="text" name="student_id_code" class="form-control" required placeholder="Contoh: INT-001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kampus/Jurusan</label>
                            <input type="text" name="department" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                </form>
                <div class="text-danger small mt-2" id="form-errors"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary btn-raise" id="save-student">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const modal = new bootstrap.Modal(document.getElementById('studentModal'));
    const table = $('#students-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: "{{ route('students.datatable') }}",
            data: function(d) {
                d.status = $('#filter-form [name="status"]').val();
                d.department = $('#filter-form [name="department"]').val();
            }
        },
        dom: "<'row mb-3'<'col-md-6'l><'col-md-6 text-end'>>rt<'row'<'col-md-5'i><'col-md-7'p>>",
        columns: [
            { data: 'nim', name: 'student_id_code' },
            { data: 'nama', name: 'full_name' },
            { data: 'jurusan', name: 'department' },
            { data: 'mulai', name: 'start_date' },
            { data: 'selesai', name: 'end_date' },
            { data: 'status_label', name: 'status', orderable: false, searchable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-end' },
        ],
        order: [[1, 'asc']],
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

    $('#add-student-btn').on('click', function() {
        $('#student-form')[0].reset();
        $('#student-id').val('');
        $('#form-errors').text('');
        modal.show();
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.search($('#filter-form [name="search"]').val());
        table.ajax.reload();
    });

    $(document).on('click', '.edit-student', function() {
        const id = $(this).data('id');
        $('#form-errors').text('');
        $.get('/students/' + id + '/edit', function(data) {
            $('#student-id').val(data.id);
            $('#student-form [name="full_name"]').val(data.full_name);
            $('#student-form [name="email"]').val(data.user.email);
            $('#student-form [name="username"]').val(data.user.username);
            $('#student-form [name="student_id_code"]').val(data.student_id_code);
            $('#student-form [name="department"]').val(data.department);
            $('#student-form [name="status"]').val(data.status);
            $('#student-form [name="start_date"]').val(data.start_date);
            $('#student-form [name="end_date"]').val(data.end_date);
            modal.show();
        });
    });

    $('#save-student').on('click', function() {
        const id = $('#student-id').val();
        const method = id ? 'PUT' : 'POST';
        const url = id ? '/students/' + id : '/students';
        $('#form-errors').text('');
        $.ajax({
            url: url,
            type: method,
            data: $('#student-form').serialize(),
            success: function(resp) {
                table.ajax.reload(null, false);
                modal.hide();
                showToast(resp.message, 'success');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Validasi gagal. Mohon periksa data yang diisi.';
                $('#form-errors').text(msg);
            }
        });
    });

    $(document).on('click', '.delete-student', function() {
        if (!confirm('Hapus mahasiswa ini? Data login juga akan terhapus.')) return;
        const id = $(this).data('id');
        $.ajax({
            url: '/students/' + id,
            type: 'DELETE',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: function(resp) {
                table.ajax.reload(null, false);
                showToast(resp.message, 'success');
            }
        });
    });

    $(document).on('click', '.toggle-status', function() {
        const id = $(this).data('id');
        $.ajax({
            url: '/students/' + id + '/status',
            type: 'PATCH',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: function(resp) {
                table.ajax.reload(null, false);
                showToast(resp.message, 'info');
            }
        });
    });
});
</script>
@endpush
