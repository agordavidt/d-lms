<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sessions belong to a program, not a cohort.
     * All learners enrolled in the program see and can join the session.
     * cohort_id removed entirely — program_id is the relationship anchor.
     */
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->enum('session_type', ['live_class', 'workshop', 'q_and_a'])->default('live_class');

            // Scheduling
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_minutes')->nullable();  

            // Google Meet
            $table->string('meet_link')->nullable();

            // Status & recording
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->string('recording_link')->nullable();

            // Attendance tracking
            $table->json('attendees')->nullable();   // array of user_id values
            $table->integer('total_attendees')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_id', 'start_time']);
            $table->index(['mentor_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};