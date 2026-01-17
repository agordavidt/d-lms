<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('session_type')->default('live_class'); // live_class, workshop, q&a, assessment
            
            // Google Meet Integration
            $table->string('meet_link')->nullable();
            $table->string('meet_id')->nullable();
            
            // Scheduling
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes')->nullable(); // calculated duration
            
            // Status and Recording
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->string('recording_link')->nullable();
            
            // Attendance tracking
            $table->json('attendees')->nullable(); // Array of user IDs who attended
            $table->integer('total_attendees')->default(0);
            
            // Additional info
            $table->text('notes')->nullable();
            $table->json('resources')->nullable(); // Links to materials, slides, etc.
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cohort_id', 'start_time']);
            $table->index(['mentor_id', 'start_time']);
            $table->index('session_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};