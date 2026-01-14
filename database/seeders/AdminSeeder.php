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
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'superadmin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'John',
            'last_name' => 'Mentor',
            'email' => 'mentor@gmail.com',
            'password' => Hash::make('Mentor@123'),
            'phone' => '+234 800 123 4567',
            'role' => 'mentor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Learner',
            'email' => 'learner@gmail.com',
            'password' => Hash::make('Learner@123'),
            'phone' => '+234 800 987 6543',
            'role' => 'learner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Additional sample learners with proper names
        User::create([
            'first_name' => 'Michael',
            'last_name' => 'Okonkwo',
            'email' => 'michael.okonkwo@example.com',
            'password' => Hash::make('Learner@123'),
            'phone' => '+234 803 111 2222',
            'role' => 'learner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Adebayo',
            'email' => 'sarah.adebayo@example.com',
            'password' => Hash::make('Learner@123'),
            'phone' => '+234 805 333 4444',
            'role' => 'learner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'David',
            'last_name' => 'Ibrahim',
            'email' => 'david.ibrahim@example.com',
            'password' => Hash::make('Learner@123'),
            'role' => 'learner',
            'status' => 'suspended',
            'email_verified_at' => now(),
        ]);
        
    }
}