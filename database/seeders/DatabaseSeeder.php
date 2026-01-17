<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Users
        $superadmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@gluper.com',
            'password' => Hash::make('Admin@123'),
            'phone' => '+234 800 000 0001',
            'role' => 'superadmin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gluper.com',
            'password' => Hash::make('Admin@123'),
            'phone' => '+234 800 000 0002',
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $mentor1 = User::create([
            'first_name' => 'John',
            'last_name' => 'Mentor',
            'email' => 'mentor@gluper.com',
            'password' => Hash::make('Mentor@123'),
            'phone' => '+234 800 000 0003',
            'role' => 'mentor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $mentor2 = User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Tech',
            'email' => 'mentor2@gluper.com',
            'password' => Hash::make('Mentor@123'),
            'phone' => '+234 800 000 0004',
            'role' => 'mentor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create 10 Learners
        $learners = [];
        for ($i = 1; $i <= 10; $i++) {
            $learners[] = User::create([
                'first_name' => "Learner{$i}",
                'last_name' => 'Student',
                'email' => "learner{$i}@gluper.com",
                'password' => Hash::make('Learner@123'),
                'phone' => "+234 800 000 " . str_pad(100 + $i, 4, '0', STR_PAD_LEFT),
                'role' => 'learner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        // Create Programs
        $programs = [
            [
                'name' => 'Fullstack Web Development',
                'slug' => 'fullstack-web-development',
                'description' => 'Master modern web development from front-end to back-end',
                'overview' => 'Learn HTML, CSS, JavaScript, React, Node.js, and deploy production-ready applications.',
                'duration' => '12 Weeks',
                'price' => 60000,
                'discount_percentage' => 10,
                'status' => 'active',
                'max_students' => 30,
                'features' => [
                    'Live classes via Google Meet (2x per week)',
                    'Real-world projects and assignments',
                    'Dedicated WhatsApp community',
                    'Career guidance and mentorship',
                    'Certificate of completion'
                ],
                'requirements' => [
                    'Basic computer skills',
                    'Laptop/Desktop with stable internet',
                    'Commitment to attend live sessions',
                    'Passion for learning'
                ]
            ],
            [
                'name' => 'Product Design (UI/UX)',
                'slug' => 'product-design-ui-ux',
                'description' => 'Create beautiful and user-friendly digital experiences',
                'overview' => 'Learn Figma, user research, wireframing, prototyping, and design thinking principles.',
                'duration' => '10 Weeks',
                'price' => 50000,
                'discount_percentage' => 10,
                'status' => 'active',
                'max_students' => 25,
                'features' => [
                    'Live design critiques and feedback sessions',
                    'Portfolio building guidance',
                    'Industry-standard tools (Figma, Adobe XD)',
                    'Client project simulation',
                    'Job-ready portfolio'
                ],
                'requirements' => [
                    'No prior design experience needed',
                    'Creative mindset',
                    'Laptop with 8GB RAM minimum'
                ]
            ],
            [
                'name' => 'Data Analytics with Python',
                'slug' => 'data-analytics-python',
                'description' => 'Turn data into actionable insights using Python and modern tools',
                'overview' => 'Master Python, Pandas, NumPy, data visualization, SQL, and statistical analysis.',
                'duration' => '12 Weeks',
                'price' => 55000,
                'discount_percentage' => 10,
                'status' => 'active',
                'max_students' => 20,
                'features' => [
                    'Hands-on data projects',
                    'Real datasets from industry',
                    'Portfolio of 5+ analysis projects',
                    'SQL and database fundamentals',
                    'Interview preparation'
                ],
                'requirements' => [
                    'Basic mathematics knowledge',
                    'Logical thinking skills',
                    'No coding experience needed'
                ]
            ]
        ];

        $createdPrograms = [];
        foreach ($programs as $programData) {
            $createdPrograms[] = Program::create($programData);
        }

        // Create Cohorts
        $cohorts = [];
        foreach ($createdPrograms as $index => $program) {
            $cohorts[] = Cohort::create([
                'program_id' => $program->id,
                'name' => now()->addMonth()->format('M Y') . ' Cohort',
                'code' => strtoupper(substr($program->slug, 0, 3)) . '-' . now()->addMonth()->format('MY'),
                'start_date' => now()->addMonth(),
                'end_date' => now()->addMonths(4),
                'status' => 'upcoming',
                'max_students' => $program->max_students,
                'enrolled_count' => 0,
                'whatsapp_link' => 'https://chat.whatsapp.com/sample-link-' . $index,
            ]);
        }

        // Enroll learners in programs
        foreach ($learners as $index => $learner) {
            // Enroll each learner in 1-2 programs
            $programsToEnroll = rand(1, 2);
            $selectedPrograms = array_rand($createdPrograms, $programsToEnroll);
            
            if (!is_array($selectedPrograms)) {
                $selectedPrograms = [$selectedPrograms];
            }

            foreach ($selectedPrograms as $programIndex) {
                $program = $createdPrograms[$programIndex];
                $cohort = $cohorts[$programIndex];

                // Create enrollment
                $enrollment = Enrollment::create([
                    'user_id' => $learner->id,
                    'program_id' => $program->id,
                    'cohort_id' => $cohort->id,
                    'status' => 'active',
                    'enrolled_at' => now()->subDays(rand(1, 30)),
                    'progress_percentage' => rand(10, 85),
                ]);

                // Increment cohort enrollment count
                $cohort->increment('enrolled_count');

                // Create successful payment
                Payment::create([
                    'user_id' => $learner->id,
                    'enrollment_id' => $enrollment->id,
                    'program_id' => $program->id,
                    'reference' => 'REF-SEED-' . strtoupper(uniqid()),
                    'amount' => $program->price,
                    'discount_amount' => ($program->price * $program->discount_percentage) / 100,
                    'final_amount' => $program->price - (($program->price * $program->discount_percentage) / 100),
                    'payment_method' => 'simulation',
                    'status' => 'successful',
                    'payment_plan' => 'one-time',
                    'remaining_balance' => 0,
                    'paid_at' => now()->subDays(rand(1, 30)),
                    'metadata' => [
                        'program_name' => $program->name,
                        'user_name' => $learner->name,
                        'user_email' => $learner->email,
                        'seeded' => true,
                    ]
                ]);
            }
        }

        // Create sample sessions
        foreach ($cohorts as $index => $cohort) {
            $mentor = $index % 2 == 0 ? $mentor1 : $mentor2;

            // Create 5 sessions for each cohort
            for ($i = 1; $i <= 5; $i++) {
                $startDate = now()->addWeek($i);
                $sessionTypes = ['live_class', 'workshop', 'q&a', 'assessment'];

                LiveSession::create([
                    'program_id' => $cohort->program_id,
                    'cohort_id' => $cohort->id,
                    'mentor_id' => $mentor->id,
                    'title' => "Week {$i}: Module {$i}",
                    'description' => "Comprehensive session covering module {$i} topics and practical exercises.",
                    'session_type' => $sessionTypes[array_rand($sessionTypes)],
                    'meet_link' => 'https://meet.google.com/sample-' . uniqid(),
                    'start_time' => $startDate->setTime(15, 0),
                    'end_time' => $startDate->copy()->setTime(17, 0),
                    'status' => 'scheduled',
                ]);
            }

            // Create 2 completed sessions with attendance
            for ($i = 1; $i <= 2; $i++) {
                $pastDate = now()->subWeeks($i);
                $enrolledStudents = Enrollment::where('cohort_id', $cohort->id)
                    ->pluck('user_id')
                    ->toArray();

                // Random attendance (70-100% of enrolled students)
                $attendeeCount = (int)(count($enrolledStudents) * (rand(70, 100) / 100));
                $attendees = array_slice($enrolledStudents, 0, $attendeeCount);

                LiveSession::create([
                    'program_id' => $cohort->program_id,
                    'cohort_id' => $cohort->id,
                    'mentor_id' => $mentor->id,
                    'title' => "Week -{$i}: Introduction Module",
                    'description' => "Completed session covering introductory topics.",
                    'session_type' => 'live_class',
                    'meet_link' => 'https://meet.google.com/sample-past-' . uniqid(),
                    'start_time' => $pastDate->setTime(15, 0),
                    'end_time' => $pastDate->copy()->setTime(17, 0),
                    'status' => 'completed',
                    'attendees' => $attendees,
                    'total_attendees' => count($attendees),
                    'recording_link' => 'https://drive.google.com/sample-recording-' . uniqid(),
                ]);
            }
        }

        
    }
}