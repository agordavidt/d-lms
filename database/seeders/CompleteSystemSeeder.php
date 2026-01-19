<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Program;
use App\Models\ProgramModule;
use App\Models\ModuleWeek;
use App\Models\WeekContent;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\LiveSession;
use App\Models\WeekProgress;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CompleteSystemSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Users
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gluper.com',
            'password' => Hash::make('password'),
            'phone' => '08012345678',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $mentor = User::create([
            'first_name' => 'John',
            'last_name' => 'Mentor',
            'email' => 'mentor@gluper.com',
            'password' => Hash::make('password'),
            'phone' => '08012345679',
            'role' => 'mentor',
            'status' => 'active',
        ]);

        $learner1 = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Learner',
            'email' => 'learner@gluper.com',
            'password' => Hash::make('password'),
            'phone' => '08012345680',
            'role' => 'learner',
            'status' => 'active',
        ]);

        $learner2 = User::create([
            'first_name' => 'Mike',
            'last_name' => 'Student',
            'email' => 'student@gluper.com',
            'password' => Hash::make('password'),
            'phone' => '08012345681',
            'role' => 'learner',
            'status' => 'active',
        ]);

        // 2. Create Programs
        $dataAnalytics = Program::create([
            'name' => 'Data Analytics Fundamentals',
            'slug' => 'data-analytics-fundamentals',
            'description' => 'Master the fundamentals of data analytics and become a data-driven professional',
            'overview' => 'This comprehensive 8-week program will take you from beginner to competent data analyst. Learn Excel, SQL, Python, and data visualization tools.',
            'duration' => '8 Weeks',
            'price' => 150000,
            'discount_percentage' => 10,
            'status' => 'active',
            'features' => [
                'Hands-on projects with real datasets',
                'Live sessions with industry experts',
                'Certificate of completion',
                'Lifetime access to course materials',
                'Career guidance and mentorship'
            ],
            'requirements' => [
                'Basic computer skills',
                'Commitment to 10-15 hours per week',
                'Willingness to learn'
            ],
        ]);

        $webDev = Program::create([
            'name' => 'Full-Stack Web Development',
            'slug' => 'full-stack-web-development',
            'description' => 'Build modern web applications from front-end to back-end',
            'overview' => 'Learn to build complete web applications using HTML, CSS, JavaScript, React, Node.js, and databases.',
            'duration' => '12 Weeks',
            'price' => 200000,
            'discount_percentage' => 10,
            'status' => 'active',
            'features' => [
                'Build 5 portfolio projects',
                'Learn modern frameworks',
                'API development',
                'Database design',
                'Deployment strategies'
            ],
            'requirements' => [
                'Basic HTML/CSS knowledge helpful but not required',
                'Access to a computer',
                '15-20 hours per week'
            ],
        ]);

        // 3. Create Modules for Data Analytics
        $module1 = ProgramModule::create([
            'program_id' => $dataAnalytics->id,
            'title' => 'Module 1: Introduction to Data Analytics',
            'description' => 'Get started with the basics of data analytics and Excel',
            'order' => 1,
            'duration_weeks' => 2,
            'status' => 'published',
            'learning_objectives' => [
                'Understand what data analytics is and its importance',
                'Master Excel fundamentals',
                'Learn basic data cleaning techniques',
                'Create simple charts and visualizations'
            ]
        ]);

        $module2 = ProgramModule::create([
            'program_id' => $dataAnalytics->id,
            'title' => 'Module 2: SQL and Database Fundamentals',
            'description' => 'Learn to query and manage databases using SQL',
            'order' => 2,
            'duration_weeks' => 3,
            'status' => 'published',
            'learning_objectives' => [
                'Write SQL queries to extract data',
                'Understand database design principles',
                'Join multiple tables',
                'Aggregate and filter data'
            ]
        ]);

        $module3 = ProgramModule::create([
            'program_id' => $dataAnalytics->id,
            'title' => 'Module 3: Data Visualization and Python Basics',
            'description' => 'Create compelling visualizations and introduction to Python',
            'order' => 3,
            'duration_weeks' => 3,
            'status' => 'published',
            'learning_objectives' => [
                'Use Tableau/Power BI for visualizations',
                'Python programming basics',
                'Data manipulation with Pandas',
                'Create automated reports'
            ]
        ]);

        // 4. Create Weeks for Module 1
        $week1 = ModuleWeek::create([
            'program_module_id' => $module1->id,
            'title' => 'Introduction to Data and Excel Basics',
            'description' => 'Understand the data analytics landscape and master Excel fundamentals',
            'week_number' => 1,
            'order' => 1,
            'status' => 'published',
            'has_assessment' => false,
            'learning_outcomes' => [
                'Define data analytics and its business applications',
                'Navigate Excel interface confidently',
                'Perform basic calculations and formulas',
                'Format data for analysis'
            ]
        ]);

        $week2 = ModuleWeek::create([
            'program_module_id' => $module1->id,
            'title' => 'Data Cleaning and Advanced Excel',
            'description' => 'Learn professional data cleaning techniques and advanced Excel features',
            'week_number' => 2,
            'order' => 2,
            'status' => 'published',
            'has_assessment' => true,
            'assessment_pass_percentage' => 70,
            'learning_outcomes' => [
                'Clean messy datasets',
                'Use VLOOKUP and HLOOKUP',
                'Create pivot tables',
                'Build dynamic dashboards'
            ]
        ]);

        // 5. Create Weeks for Module 2
        $week3 = ModuleWeek::create([
            'program_module_id' => $module2->id,
            'title' => 'SQL Fundamentals and Basic Queries',
            'description' => 'Introduction to SQL and writing your first queries',
            'week_number' => 3,
            'order' => 1,
            'status' => 'published',
            'has_assessment' => false,
            'learning_outcomes' => [
                'Understand relational databases',
                'Write SELECT statements',
                'Filter data with WHERE clause',
                'Sort and limit results'
            ]
        ]);

        $week4 = ModuleWeek::create([
            'program_module_id' => $module2->id,
            'title' => 'Joining Tables and Aggregations',
            'description' => 'Master SQL joins and aggregate functions',
            'week_number' => 4,
            'order' => 2,
            'status' => 'published',
            'has_assessment' => false,
            'learning_outcomes' => [
                'Perform INNER, LEFT, and RIGHT joins',
                'Use GROUP BY and aggregate functions',
                'Write subqueries',
                'Create calculated fields'
            ]
        ]);

        // 6. Create Content for Week 1
        WeekContent::create([
            'module_week_id' => $week1->id,
            'created_by' => $mentor->id,
            'title' => 'Welcome to Data Analytics',
            'description' => 'An introduction to the exciting world of data analytics',
            'content_type' => 'video',
            'order' => 1,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_duration_minutes' => 15,
            'is_required' => true,
            'status' => 'published'
        ]);

        WeekContent::create([
            'module_week_id' => $week1->id,
            'created_by' => $mentor->id,
            'title' => 'Excel Basics Guide',
            'description' => 'Comprehensive guide to Excel fundamentals',
            'content_type' => 'pdf',
            'order' => 2,
            'file_path' => 'content/pdfs/excel-basics.pdf',
            'is_required' => true,
            'is_downloadable' => true,
            'status' => 'published',
            'metadata' => [
                'original_name' => 'Excel Basics Guide.pdf',
                'file_size' => 2048000
            ]
        ]);

        WeekContent::create([
            'module_week_id' => $week1->id,
            'created_by' => $mentor->id,
            'title' => 'Excel Practice Exercises',
            'description' => 'Download practice files and exercises',
            'content_type' => 'link',
            'order' => 3,
            'external_url' => 'https://example.com/excel-exercises',
            'is_required' => false,
            'status' => 'published'
        ]);

        WeekContent::create([
            'module_week_id' => $week1->id,
            'created_by' => $mentor->id,
            'title' => 'Understanding Data Types',
            'description' => 'Learn about different types of data in analytics',
            'content_type' => 'text',
            'order' => 4,
            'text_content' => '<h2>Data Types in Analytics</h2><p>In data analytics, we work with different types of data...</p><ul><li>Numerical Data</li><li>Categorical Data</li><li>Text Data</li><li>Date/Time Data</li></ul>',
            'is_required' => true,
            'status' => 'published'
        ]);

        // 7. Create Content for Week 2
        WeekContent::create([
            'module_week_id' => $week2->id,
            'created_by' => $mentor->id,
            'title' => 'Data Cleaning Techniques',
            'description' => 'Master the art of cleaning messy datasets',
            'content_type' => 'video',
            'order' => 1,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_duration_minutes' => 25,
            'is_required' => true,
            'status' => 'published'
        ]);

        WeekContent::create([
            'module_week_id' => $week2->id,
            'created_by' => $mentor->id,
            'title' => 'Advanced Excel Formulas',
            'description' => 'Learn VLOOKUP, IF statements, and more',
            'content_type' => 'video',
            'order' => 2,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_duration_minutes' => 30,
            'is_required' => true,
            'status' => 'published'
        ]);

        // 8. Create Cohorts
        $cohort1 = Cohort::create([
            'program_id' => $dataAnalytics->id,
            'name' => 'January 2026 Cohort',
            'code' => 'DA-JAN-2026',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7 + 56),
            'status' => 'upcoming',
            'max_students' => 30,
            'enrolled_count' => 2,
            'whatsapp_link' => 'https://chat.whatsapp.com/example'
        ]);

        $cohort2 = Cohort::create([
            'program_id' => $dataAnalytics->id,
            'name' => 'February 2026 Cohort',
            'code' => 'DA-FEB-2026',
            'start_date' => now()->addDays(35),
            'end_date' => now()->addDays(35 + 56),
            'status' => 'upcoming',
            'max_students' => 30,
            'enrolled_count' => 0
        ]);

        // 9. Create Enrollments
        $enrollment1 = Enrollment::create([
            'user_id' => $learner1->id,
            'program_id' => $dataAnalytics->id,
            'cohort_id' => $cohort1->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'progress_percentage' => 25
        ]);

        // 10. Create Payment
        Payment::create([
            'user_id' => $learner1->id,
            'enrollment_id' => $enrollment1->id,
            'program_id' => $dataAnalytics->id,
            'reference' => 'REF-' . strtoupper(Str::random(10)),
            'amount' => 150000,
            'discount_amount' => 15000,
            'final_amount' => 135000,
            'payment_method' => 'card',
            'status' => 'successful',
            'payment_plan' => 'one-time',
            'paid_at' => now()
        ]);

        // 11. Initialize Week Progress for Learner
        $weekProgress1 = WeekProgress::create([
            'user_id' => $learner1->id,
            'module_week_id' => $week1->id,
            'enrollment_id' => $enrollment1->id,
            'is_unlocked' => true,
            'is_completed' => false,
            'progress_percentage' => 50,
            'contents_completed' => 2,
            'total_contents' => 4,
            'unlocked_at' => now(),
            'started_at' => now()
        ]);

        // 12. Create Live Sessions
        LiveSession::create([
            'program_id' => $dataAnalytics->id,
            'cohort_id' => $cohort1->id,
            'mentor_id' => $mentor->id,
            'week_id' => $week1->id,
            'title' => 'Welcome Session: Introduction to Data Analytics',
            'description' => 'Meet your cohort and get oriented with the program',
            'session_type' => 'live_class',
            'meet_link' => 'https://meet.google.com/abc-defg-hij',
            'start_time' => now()->addDays(8)->setTime(18, 0),
            'end_time' => now()->addDays(8)->setTime(20, 0),
            'status' => 'scheduled'
        ]);

        LiveSession::create([
            'program_id' => $dataAnalytics->id,
            'cohort_id' => $cohort1->id,
            'mentor_id' => $mentor->id,
            'week_id' => $week1->id,
            'title' => 'Excel Basics Workshop',
            'description' => 'Hands-on Excel practice session',
            'session_type' => 'workshop',
            'meet_link' => 'https://meet.google.com/abc-defg-hij',
            'start_time' => now()->addDays(10)->setTime(18, 0),
            'end_time' => now()->addDays(10)->setTime(20, 0),
            'status' => 'scheduled'
        ]);

        
    }
}