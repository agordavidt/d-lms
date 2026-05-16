<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Create Admin
        User::create([
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@demo.com',
            'phone' => '+1234567891',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // Create Mentors (multiple for demo)
        $mentors = [
            [
                'first_name' => 'Rapheal',
                'last_name' => 'Ojo',
                'email' => 'mentor@demo.com',
                'phone' => '+1234567892',
            ],
            [
                'first_name' => 'Samson',
                'last_name' => 'Dodo',
                'email' => 'mentor2@demo.com',
                'phone' => '+1234567893',
            ],
            [
                'first_name' => 'Grace',
                'last_name' => 'Fagbeyi',
                'email' => 'mentor3@demo.com',
                'phone' => '+1234567894',
            ],
        ];

        foreach ($mentors as $mentor) {
            User::create([
                'first_name' => $mentor['first_name'],
                'last_name' => $mentor['last_name'],
                'email' => $mentor['email'],
                'phone' => $mentor['phone'],
                'password' => Hash::make('password'),
                'role' => 'mentor',
                'status' => 'active',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
        }

        // Create Learners (multiple with different statuses)
        $learners = [
            // Active verified learners
            [
                'first_name' => 'Eneh',
                'last_name' => 'Agbo',
                'email' => 'learner1@demo.com',
                'phone' => '+1234567895',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'first_name' => 'Ademola',
                'last_name' => 'Lookman',
                'email' => 'learner2@demo.com',
                'phone' => '+1234567896',
                'status' => 'active',
                'email_verified_at' => now(),
            ],   
            
        ];

        foreach ($learners as $learner) {
            User::create([
                'first_name' => $learner['first_name'],
                'last_name' => $learner['last_name'],
                'email' => $learner['email'],
                'phone' => $learner['phone'],
                'password' => Hash::make('password'),
                'role' => 'learner',
                'status' => $learner['status'],
                'email_verified_at' => $learner['email_verified_at'],
                'remember_token' => Str::random(10),
            ]);
        }
        
    }
}