<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        Program::create([
            'name' => 'Fullstack Web Development',
            'slug' => 'fullstack-web-development',
            'description' => 'Master frontend and backend web development',
            'overview' => 'Learn HTML, CSS, JavaScript, React, Node.js, and more',
            'duration' => '12 Weeks',
            'price' => 60000,
            'discount_percentage' => 10,
            'status' => 'active',
            'max_students' => 50,
            'features' => ['Live sessions', '24/7 support', 'Certificate', 'Projects'],
            'requirements' => ['Basic computer skills', 'Internet connection'],
        ]);

        Program::create([
            'name' => 'Product Design (UI/UX)',
            'slug' => 'product-design-uiux',
            'description' => 'Design beautiful and user-friendly products',
            'overview' => 'Learn Figma, user research, prototyping, and design thinking',
            'duration' => '10 Weeks',
            'price' => 50000,
            'discount_percentage' => 10,
            'status' => 'active',
            'max_students' => 40,
            'features' => ['Figma training', 'Portfolio projects', 'Certificate'],
            'requirements' => ['No prior experience needed'],
        ]);
    }
}