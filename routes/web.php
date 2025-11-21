<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware(['auth']);

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::patch('/students/{student}/status', [StudentController::class, 'toggleStatus'])->name('students.status');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/table', [ReportController::class, 'table'])->name('reports.table');
        Route::get('/reports/latest', [ReportController::class, 'latest'])->name('reports.latest');
        Route::get('/reports/{student}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{student}/export', [ReportController::class, 'export'])->name('reports.export');
    });

Route::middleware(['auth', 'student'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
});
