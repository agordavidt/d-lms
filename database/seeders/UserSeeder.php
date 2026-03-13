<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'phone' => '08012345678',
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Mentor
        User::create([
            'first_name' => 'John',
            'last_name' => 'Mentor',
            'email' => 'mentor@demo.com',
            'password' => Hash::make('password'),
            'phone' => '08012345679',
            'role' => 'mentor',
            'status' => 'active',
        ]);

        // Learner 1 (email verified)
        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Learner',
            'email' => 'learner@demo.com',
            'password' => Hash::make('password'),
            'phone' => '08012345680',
            'role' => 'learner',
            'status' => 'active',
            'email_verified_at' => Carbon::now(),
        ]);

        // Learner 2 (email verified)
        User::create([
            'first_name' => 'Mike',
            'last_name' => 'Enoja',
            'email' => 'mike@demo.com',
            'password' => Hash::make('password'),
            'phone' => '08012345681',
            'role' => 'learner',
            'status' => 'active',
            'email_verified_at' => Carbon::now(),
        ]);
    }
}