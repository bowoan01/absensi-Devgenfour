<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuickCheckinController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Pintas check-in tanpa login
Route::get('/quick-checkin', [QuickCheckinController::class, 'showForm'])->name('quick-checkin.form');
Route::post('/quick-checkin', [QuickCheckinController::class, 'submit'])->name('quick-checkin.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('admin')->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/datatable', [StudentController::class, 'datatable'])->name('students.datatable');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::patch('/students/{student}/status', [StudentController::class, 'toggleStatus'])->name('students.status');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/datatable', [ReportController::class, 'datatable'])->name('reports.datatable');
        Route::get('/reports/{student}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{student}/export', [ReportController::class, 'export'])->name('reports.export');
        Route::put('/reports/attendance/{attendance}', [ReportController::class, 'updateAttendance'])->name('reports.attendance.update');
    });

    Route::middleware('student')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
        Route::post('/attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
        Route::get('/mahasiswa/absensi/events', [AttendanceController::class, 'events'])->name('attendance.events');
    });
});
