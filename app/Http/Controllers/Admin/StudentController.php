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
                $term = $request->input('search');
                $q->where(function ($inner) use ($term) {
                    $inner->where('full_name', 'like', "%{$term}%")
                        ->orWhere('student_id_code', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('department'), fn($q) => $q->where('department', $request->input('department')))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->orderBy('full_name');

        $students = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            $table = view('partials.students.table', compact('students'))->render();
            return response()->json(['html' => $table]);
        }

        $departments = Student::select('department')->distinct()->pluck('department');
        return view('admin.students.index', compact('students', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'student_id_code' => 'required|string|max:100|unique:students,student_id_code',
            'department' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        $password = 'polindra123';
        DB::transaction(function () use ($validated, $password) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $password,
                'role' => User::ROLE_STUDENT,
                'status' => $validated['status'],
            ]);

            Student::create([
                'user_id' => $user->id,
                'full_name' => $validated['name'],
                'student_id_code' => $validated['student_id_code'],
                'department' => $validated['department'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        $html = view('partials.students.table', compact('students'))->render();

        return response()->json([
            'message' => 'Student created successfully. Temporary password: '.$password,
            'html' => $html,
        ]);
    }

    public function edit(Student $student)
    {
        return response()->json($student->load('user'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$student->user_id,
            'student_id_code' => 'required|string|max:100|unique:students,student_id_code,'.$student->id,
            'department' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($student, $validated) {
            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ]);

            $student->update([
                'full_name' => $validated['name'],
                'student_id_code' => $validated['student_id_code'],
                'department' => $validated['department'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        $html = view('partials.students.table', compact('students'))->render();

        return response()->json([
            'message' => 'Student updated successfully.',
            'html' => $html,
        ]);
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            if ($student->user) {
                $student->user->delete();
            }
            $student->delete();
        });
        Log::info('Student deleted', ['student_id' => $student->id, 'actor' => auth()->id()]);

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        $html = view('partials.students.table', compact('students'))->render();

        return response()->json([
            'message' => 'Student removed.',
            'html' => $html,
        ]);
    }

    public function toggleStatus(Student $student)
    {
        $newStatus = $student->status === User::STATUS_ACTIVE ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;
        $student->update(['status' => $newStatus]);
        $student->user()->update(['status' => $newStatus]);

        $students = Student::with('user')->orderBy('full_name')->paginate(10);
        $html = view('partials.students.table', compact('students'))->render();

        return response()->json([
            'message' => 'Status updated.',
            'html' => $html,
        ]);
    }
}
