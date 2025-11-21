<table class="table table-hover align-middle">
    <thead class="table-light sticky-top">
        <tr>
            <th>Nama</th>
            <th>Kode Peserta</th>
            <th>Departemen</th>
            <th>Mulai</th>
            <th>Selesai</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
            @php
                $start = $student->start_date ? $student->start_date->locale('id') : null;
                $end = $student->end_date ? $student->end_date->locale('id') : null;
            @endphp
            <tr>
                <td>{{ $student->full_name }}</td>
                <td>{{ $student->student_id_code }}</td>
                <td>{{ $student->department }}</td>
                <td>{{ $start ? $start->translatedFormat('d F Y') : '—' }}</td>
                <td>{{ $end ? $end->translatedFormat('d F Y') : '—' }}</td>
                <td><span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">{{ $student->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td class="text-end">
                    <a href="{{ route('reports.show', $student) }}" class="btn btn-outline-primary btn-sm btn-raise">Lihat Laporan</a>
                    <button class="btn btn-outline-secondary btn-sm btn-raise edit-student" data-id="{{ $student->id }}">Ubah</button>
                    <button class="btn btn-outline-warning btn-sm btn-raise toggle-status" data-id="{{ $student->id }}">{{ $student->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    <button class="btn btn-outline-danger btn-sm btn-raise delete-student" data-id="{{ $student->id }}">Hapus</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data mahasiswa.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="d-flex justify-content-end">{{ $students->links() }}</div>
