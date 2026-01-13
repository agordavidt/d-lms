<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'superadmin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Test Mentor',
            'email' => 'mentor@gmail.com',
            'password' => Hash::make('Mentor@123'),
            'role' => 'mentor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Test Learner',
            'email' => 'learner@gmail.com',
            'password' => Hash::make('Learner@123'),
            'role' => 'learner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}