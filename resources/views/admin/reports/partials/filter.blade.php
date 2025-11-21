<div class="card shadow-sm border-0 mb-3 card-hover">
    <div class="card-body">
        <form id="report-filter" class="row gy-2 gx-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Mahasiswa</label>
                <select name="student_id" class="form-select">
                    <option value="">Semua Mahasiswa</option>
                    @foreach($students as $stu)
                        <option value="{{ $stu->id }}" @selected(($filters['student_id'] ?? '') == $stu->id)>{{ $stu->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="present" @selected(($filters['status'] ?? '') === 'present')>Hadir</option>
                    <option value="late" @selected(($filters['status'] ?? '') === 'late')>Terlambat</option>
                    <option value="absent" @selected(($filters['status'] ?? '') === 'absent')>Tidak Hadir</option>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-primary btn-raise" type="submit">Terapkan</button>
            </div>
        </form>
    </div>
</div>
