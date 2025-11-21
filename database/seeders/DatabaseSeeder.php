<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        $studentUser = User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Sample Student',
                'username' => 'student',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STUDENT,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        Student::updateOrCreate(
            ['user_id' => $studentUser->id],
            [
                'full_name' => 'Sample Student',
                'student_id_code' => 'INT-001',
                'department' => 'Engineering',
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(),
                'status' => 'active',
            ]
        );
    }
}
