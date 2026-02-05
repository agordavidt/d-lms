<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->integer('attempt_number');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('total_questions');
            $table->integer('total_points');
            $table->decimal('score_earned', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->json('answers')->nullable(); // Store all answers with correctness
            $table->enum('status', ['in_progress', 'submitted', 'abandoned'])->default('in_progress');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'enrollment_id']);
            $table->index(['assessment_id', 'user_id']);
            $table->index('status');
            $table->unique(['assessment_id', 'user_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempts');
    }
};