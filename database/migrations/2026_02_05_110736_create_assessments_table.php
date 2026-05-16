<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Assessments ───────────────────────────────────────────────────────
        // pass_percentage is ONLY meaningful for the final exam (fixed at 75).
        // Weekly assessments always require 100% — not stored, enforced in code.
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->integer('time_limit_minutes')->nullable(); // null = no limit (typical for weekly)
            $table->boolean('randomize_questions')->default(false);

            // Final exam flag — only one allowed per program
            // When is_final = true: pass_percentage applies (default 75), 48hr cooldown on fail
            // When is_final = false: pass threshold is always 100%, no cooldown
            $table->boolean('is_final')->default(false);
            $table->integer('pass_percentage')->default(75); // only read when is_final = true

            $table->timestamps();
            $table->softDeletes();

            $table->unique('module_week_id'); // one assessment per week
            $table->index('created_by');
            $table->index('is_final');
        });

        // ── Assessment Questions ──────────────────────────────────────────────
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();

            $table->enum('question_type', ['multiple_choice', 'true_false', 'multiple_select'])
                  ->default('multiple_choice');
            $table->text('question_text');
            $table->json('options');
            $table->json('correct_answer');
            $table->text('explanation')->nullable();
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['assessment_id', 'order']);
        });

        // ── Assessment Attempts ───────────────────────────────────────────────
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();

            $table->integer('attempt_number');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();

            // Only set on failed final exam attempts — gates the next retry
            $table->timestamp('next_attempt_at')->nullable();

            $table->integer('time_spent_seconds')->default(0);
            $table->integer('total_questions');
            $table->integer('total_points');
            $table->decimal('score_earned', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);

            $table->json('answers')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'abandoned'])->default('in_progress');

            $table->timestamps();

            $table->unique(['assessment_id', 'user_id', 'attempt_number']);
            $table->index(['user_id', 'enrollment_id']);
            $table->index(['assessment_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};