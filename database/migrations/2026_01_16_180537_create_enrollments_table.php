<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete(); // nullable — auto-assigned
            $table->string('enrollment_number')->nullable()->unique();

            // Enrollment status
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->date('enrolled_at');
            $table->date('completed_at')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);

            // Graduation workflow
            $table->enum('graduation_status', ['active', 'pending_review', 'graduated', 'dropped'])
                  ->default('active');
            $table->decimal('final_grade_avg', 5, 2)->nullable();
            $table->decimal('weekly_assessment_avg', 5, 2)->nullable();

            // Graduation approval
            $table->timestamp('graduation_requested_at')->nullable();
            $table->timestamp('graduation_approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            // Certificate
            $table->string('certificate_key')->unique()->nullable();
            $table->timestamp('certificate_issued_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['cohort_id', 'status']);
            $table->unique(['user_id', 'program_id']);  // One enrollment per learner per program
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};