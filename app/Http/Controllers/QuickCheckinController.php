<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class QuickCheckinController extends Controller
{
    public function showForm()
    {
        return view('quick-checkin');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'nim' => 'required|string',
            'password' => 'required|string',
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $student = Student::with('user')->where('student_id_code', $data['nim'])->first();
        $result = [
            'status' => 'danger',
            'title' => 'NIM atau password salah.',
            'details' => null,
        ];

        if (! $student || ! $student->user || ! Hash::check($data['password'], $student->user->password)) {
            return view('quick-checkin', ['result' => $result]);
        }

        if ($student->status !== Student::STATUS_ACTIVE || $student->user->status !== User::STATUS_ACTIVE) {
            return view('quick-checkin', [
                'result' => [
                    'status' => 'danger',
                    'title' => 'Akun tidak aktif.',
                    'details' => 'Hubungi admin untuk mengaktifkan akun Anda.',
                ],
            ]);
        }

        $now = Carbon::now(config('app.timezone'));
        $today = $now->toDateString();

        $record = Attendance::where('student_id', $student->id)
            ->whereDate('date', $today)
            ->first();

        if ($record && $record->check_in_at) {
            $checkInTime = $record->check_in_at->timezone(config('app.timezone'))->format('H.i') . ' WIB';
            $result = [
                'status' => 'warning',
                'title' => 'Anda sudah check-in hari ini.',
                'details' => 'Waktu check-in: ' . $checkInTime,
            ];
            return view('quick-checkin', ['result' => $result]);
        }

        $record = $record ?? new Attendance([
            'student_id' => $student->id,
            'date' => $today,
        ]);

        $statusLabels = [
            Attendance::STATUS_PRESENT => 'Hadir',
            Attendance::STATUS_LATE => 'Terlambat',
            Attendance::STATUS_ABSENT => 'Tidak Hadir',
        ];

        $record->check_in_at = $now;
        $record->status = Attendance::determineStatus($now);

        $autoCheckout = Carbon::today(config('app.timezone'))->setTime(16, 0, 0);
        if ($autoCheckout->lessThan($now)) {
            $autoCheckout = $now;
        }
        $record->check_out_at = $autoCheckout;
        if (! $record->note) {
            $record->note = 'Check-in pintas';
        }
        $record->save();

        $result = [
            'status' => 'success',
            'title' => 'Check-in berhasil.',
            'details' => 'Check-out otomatis diset pada ' . $record->check_out_at->timezone(config('app.timezone'))->format('H.i') . ' WIB. Status: ' . ($statusLabels[$record->status] ?? ucfirst($record->status)),
        ];

        return view('quick-checkin', ['result' => $result]);
    }
}
