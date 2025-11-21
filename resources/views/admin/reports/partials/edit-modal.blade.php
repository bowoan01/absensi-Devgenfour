<div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ubah Data Absensi</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="attendance-form">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Waktu Masuk (WIB)</label>
                        <input type="datetime-local" name="check_in_at" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Waktu Pulang (WIB)</label>
                        <input type="datetime-local" name="check_out_at" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="present">Hadir</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </form>
                <div class="text-danger small" id="attendance-errors"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary btn-raise" id="save-attendance">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
