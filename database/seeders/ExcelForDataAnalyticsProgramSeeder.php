<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\ProgramModule;
use App\Models\ModuleWeek;

class ExcelForDataAnalyticsProgramSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Program
        |--------------------------------------------------------------------------
        */

        $program = Program::updateOrCreate(
            [
                'slug' => 'excel-for-data-analytics',
            ],
            [
                'mentor_id'   => 1, // Change as needed
                'name'        => 'Excel for Data Analytics',
                'description' => 'Master Microsoft Excel for data analytics, reporting, dashboard development, business intelligence, and data-driven decision making.',
                'duration'    => '14 Weeks',
                'price'       => 0,
                'status'      => 'active',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Module 1
        |--------------------------------------------------------------------------
        */

        $module1 = ProgramModule::updateOrCreate(
            [
                'program_id' => $program->id,
                'order'      => 1,
            ],
            [
                'title'       => 'Excel Foundations',
                'description' => 'Learn the fundamentals of Excel, workbook navigation, data entry, formatting, and core formulas.',
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module1->id,
                'order'             => 1,
            ],
            [
                'title'           => 'Introduction to Excel & Workbook Navigation',
                'week_number'     => 1,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module1->id,
                'order'             => 2,
            ],
            [
                'title'           => 'Data Entry, Formatting & Data Types',
                'week_number'     => 2,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module1->id,
                'order'             => 3,
            ],
            [
                'title'           => 'Essential Formulas & Functions',
                'week_number'     => 3,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Module 2
        |--------------------------------------------------------------------------
        */

        $module2 = ProgramModule::updateOrCreate(
            [
                'program_id' => $program->id,
                'order'      => 2,
            ],
            [
                'title'       => 'Data Preparation',
                'description' => 'Prepare and clean datasets for reliable analysis.',
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module2->id,
                'order'             => 1,
            ],
            [
                'title'           => 'Sorting, Filtering & Excel Tables',
                'week_number'     => 4,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module2->id,
                'order'             => 2,
            ],
            [
                'title'           => 'Data Cleaning Techniques',
                'week_number'     => 5,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module2->id,
                'order'             => 3,
            ],
            [
                'title'           => 'Text Functions for Analytics',
                'week_number'     => 6,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Module 3
        |--------------------------------------------------------------------------
        */

        $module3 = ProgramModule::updateOrCreate(
            [
                'program_id' => $program->id,
                'order'      => 3,
            ],
            [
                'title'       => 'Analytical Excel Skills',
                'description' => 'Learn analytical functions and tools commonly used by data analysts.',
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module3->id,
                'order'             => 1,
            ],
            [
                'title'           => 'Logical Functions',
                'week_number'     => 7,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module3->id,
                'order'             => 2,
            ],
            [
                'title'           => 'Lookup Functions (VLOOKUP, XLOOKUP, INDEX & MATCH)',
                'week_number'     => 8,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module3->id,
                'order'             => 3,
            ],
            [
                'title'           => 'Conditional Formatting',
                'week_number'     => 9,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Module 4
        |--------------------------------------------------------------------------
        */

        $module4 = ProgramModule::updateOrCreate(
            [
                'program_id' => $program->id,
                'order'      => 4,
            ],
            [
                'title'       => 'Data Analysis & Reporting',
                'description' => 'Transform raw data into meaningful insights using pivot tables, charts, and dashboards.',
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module4->id,
                'order'             => 1,
            ],
            [
                'title'           => 'Pivot Tables',
                'week_number'     => 10,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module4->id,
                'order'             => 2,
            ],
            [
                'title'           => 'Pivot Charts',
                'week_number'     => 11,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module4->id,
                'order'             => 3,
            ],
            [
                'title'           => 'Dashboards & Reporting',
                'week_number'     => 12,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Module 5
        |--------------------------------------------------------------------------
        */

        $module5 = ProgramModule::updateOrCreate(
            [
                'program_id' => $program->id,
                'order'      => 5,
            ],
            [
                'title'       => 'Capstone Project',
                'description' => 'Apply everything learned throughout the program to complete an end-to-end Excel analytics project.',
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $module5->id,
                'order'             => 1,
            ],
            [
                'title'           => 'End-to-End Excel Analytics Project',
                'week_number'     => 13,
                'has_assessment'  => true,
                'is_final_week'   => false,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Final Exam Module
        |--------------------------------------------------------------------------
        */

        $finalModule = ProgramModule::updateOrCreate(
            [
                'program_id' => $program->id,
                'order'      => 6,
            ],
            [
                'title'       => 'Final Assessment',
                'description' => 'Comprehensive final examination covering all concepts taught throughout the program.',
            ]
        );

        ModuleWeek::updateOrCreate(
            [
                'program_module_id' => $finalModule->id,
                'order'             => 1,
            ],
            [
                'title'           => 'Final Examination',
                'week_number'     => 14,
                'has_assessment'  => true,
                'is_final_week'   => true,
            ]
        );
    }
}