<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.students.index');
    }

    public function datatable(Request $request)
    {
        $query = Student::with('user')
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('department'), fn($q) => $q->where('department', 'like', '%' . $request->input('department') . '%'));

        return DataTables::eloquent($query)
            ->addColumn('nim', fn(Student $student) => e($student->student_id_code))
            ->addColumn('nama', fn(Student $student) => e($student->full_name))
            ->addColumn('jurusan', fn(Student $student) => e($student->department))
            ->addColumn('mulai', fn(Student $student) => $student->start_date ? $student->start_date->locale('id')->translatedFormat('d F Y') : '—')
            ->addColumn('selesai', fn(Student $student) => $student->end_date ? $student->end_date->locale('id')->translatedFormat('d F Y') : '—')
            ->addColumn('status_label', function (Student $student) {
                $label = $student->status === Student::STATUS_ACTIVE ? 'Aktif' : 'Nonaktif';
                $badge = $student->status === Student::STATUS_ACTIVE ? 'success' : 'secondary';
                return '<span class="badge bg-' . $badge . '">' . $label . '</span>';
            })
            ->addColumn('aksi', function (Student $student) {
                return '
                    <div class="d-flex justify-content-end gap-1">
                        <a href="' . route('reports.show', $student) . '" class="btn btn-outline-primary btn-sm btn-raise">Lihat Laporan</a>
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-raise edit-student" data-id="' . $student->id . '">Ubah</button>
                        <button type="button" class="btn btn-outline-warning btn-sm btn-raise toggle-status" data-id="' . $student->id . '">' . ($student->status === Student::STATUS_ACTIVE ? 'Nonaktifkan' : 'Aktifkan') . '</button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-raise delete-student" data-id="' . $student->id . '">Hapus</button>
                    </div>
                ';
            })
            ->rawColumns(['status_label', 'aksi'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:255|unique:users,username',
            'student_id_code' => 'required|string|max:100|unique:students,student_id_code',
            'department' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ], $this->validationMessages(), $this->attributeLabels());

        DB::transaction(function () use ($data) {
            $defaultPassword = 'devgen123456';

            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($defaultPassword),
                'role' => User::ROLE_STUDENT,
                'status' => $data['status'],
            ]);

            Student::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'student_id_code' => $data['student_id_code'],
                'department' => $data['department'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
            ]);
        });

        // Log::info('Student created', ['user' => auth()->id(), 'student' => $data['student_id_code']]);

        return response()->json(['message' => 'Mahasiswa berhasil ditambahkan.']);
    }

    public function edit(Student $student)
    {
        $student->load('user');
        return response()->json($student);
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $student->user_id,
            'password' => 'nullable|string|min:8',
            'student_id_code' => 'required|string|max:100|unique:students,student_id_code,' . $student->id,
            'department' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ], $this->validationMessages(), $this->attributeLabels());

        DB::transaction(function () use ($data, $student) {
            $student->user->update([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'status' => $data['status'],
                'password' => $data['password'] ? Hash::make($data['password']) : $student->user->password,
            ]);

            $student->update([
                'full_name' => $data['full_name'],
                'student_id_code' => $data['student_id_code'],
                'department' => $data['department'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
            ]);
        });

        // Log::info('Student updated', ['user' => auth()->id(), 'student' => $student->id]);

        return response()->json(['message' => 'Data mahasiswa berhasil diperbarui.']);
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            $student->user()->delete();
            $student->delete();
        });

        // Log::info('Student deleted', ['user' => auth()->id(), 'student' => $student->id]);

        return response()->json(['message' => 'Mahasiswa berhasil dihapus.']);
    }

    public function toggleStatus(Student $student)
    {
        $newStatus = $student->status === Student::STATUS_ACTIVE ? Student::STATUS_INACTIVE : Student::STATUS_ACTIVE;
        $student->update(['status' => $newStatus]);
        $student->user()->update(['status' => $newStatus]);

        // Log::info('Student status toggled', ['user' => auth()->id(), 'student' => $student->id, 'status' => $newStatus]);

        return response()->json(['message' => 'Status mahasiswa diperbarui.']);
    }

    protected function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute harus berformat email yang valid.',
            'unique' => ':attribute sudah digunakan.',
            'string' => ':attribute harus berupa teks.',
            'min' => ':attribute minimal :min karakter.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'after_or_equal' => ':attribute harus sama atau setelah tanggal mulai.',
            'in' => 'Pilihan :attribute tidak valid.',
        ];
    }

    protected function attributeLabels(): array
    {
        return [
            'full_name' => 'Nama lengkap',
            'email' => 'Email',
            'username' => 'Username',
            'password' => 'Kata sandi',
            'student_id_code' => 'NIM',
            'department' => 'Departemen/jurusan',
            'start_date' => 'Tanggal mulai',
            'end_date' => 'Tanggal selesai',
            'status' => 'Status',
        ];
    }
}
