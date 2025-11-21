<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('user')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', "%{$search}%")
                        ->orWhere('student_id_code', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            })
            ->when($request->filled('department'), function ($q) use ($request) {
                $q->where('department', $request->input('department'));
            })
            ->orderBy('full_name');

        $students = $query->paginate(10);

        if ($request->ajax()) {
            $html = view('partials.students.table', compact('students'))->render();
            return response()->json(['html' => $html]);
        }

        return view('admin.students.index', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'student_id_code' => 'required|string|max:100|unique:students,student_id_code',
            'department' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ], $this->validationMessages(), $this->attributeLabels());

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
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

        Log::info('Student created', ['user' => auth()->id(), 'student' => $data['student_id_code']]);

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        return response()->json([
            'message' => 'Mahasiswa berhasil ditambahkan.',
            'html' => view('partials.students.table', compact('students'))->render(),
        ]);
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

        Log::info('Student updated', ['user' => auth()->id(), 'student' => $student->id]);

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        return response()->json([
            'message' => 'Data mahasiswa berhasil diperbarui.',
            'html' => view('partials.students.table', compact('students'))->render(),
        ]);
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            $student->user()->delete();
            $student->delete();
        });

        Log::info('Student deleted', ['user' => auth()->id(), 'student' => $student->id]);

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        return response()->json([
            'message' => 'Mahasiswa berhasil dihapus.',
            'html' => view('partials.students.table', compact('students'))->render(),
        ]);
    }

    public function toggleStatus(Student $student)
    {
        $newStatus = $student->status === Student::STATUS_ACTIVE ? Student::STATUS_INACTIVE : Student::STATUS_ACTIVE;
        $student->update(['status' => $newStatus]);
        $student->user()->update(['status' => $newStatus]);

        Log::info('Student status toggled', ['user' => auth()->id(), 'student' => $student->id, 'status' => $newStatus]);

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        return response()->json([
            'message' => 'Status mahasiswa diperbarui.',
            'html' => view('partials.students.table', compact('students'))->render(),
        ]);
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
            'student_id_code' => 'Kode peserta',
            'department' => 'Departemen/jurusan',
            'start_date' => 'Tanggal mulai',
            'end_date' => 'Tanggal selesai',
            'status' => 'Status',
        ];
    }
}
