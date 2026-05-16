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
            
            // CHANGED: Made cohort_id nullable and set to nullOnDelete
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('enrollment_number')->nullable()->unique();
 
            // Enrollment status
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->date('enrolled_at');
            $table->date('completed_at')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
 
            // Graduation workflow
            $table->enum('graduation_status', ['active', 'pending_review', 'graduated', 'dropped'])
                  ->default('active');
 
            // Final exam score stored here for the admin review screen
            $table->decimal('final_exam_score', 5, 2)->nullable();
 
            // Graduation approval
            $table->timestamp('graduation_requested_at')->nullable();
            $table->timestamp('graduation_approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
 
            // Certificate
            $table->string('certificate_key')->unique()->nullable();
            $table->timestamp('certificate_issued_at')->nullable();
 
            $table->timestamps();
            $table->softDeletes();
 
            $table->unique(['user_id', 'program_id']); // one enrollment per learner per program
            $table->index(['user_id', 'status']);
            $table->index(['cohort_id', 'status']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};