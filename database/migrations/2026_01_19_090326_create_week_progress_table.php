<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks a learner's progress through each week.
     * Combines the base progress fields and assessment tracking fields
     * previously spread across two migration files.
     */
    public function up(): void
    {
        Schema::create('week_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();

            // Week access & completion
            $table->boolean('is_unlocked')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->integer('progress_percentage')->default(0); 
            $table->integer('contents_completed')->default(0);
            $table->integer('total_contents')->default(0);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Assessment tracking
            $table->decimal('assessment_score', 5, 2)->nullable();    // best score achieved
            $table->boolean('assessment_passed')->default(false);      // gate: must be true to complete week
            $table->integer('assessment_attempts')->default(0);
            $table->timestamp('last_assessment_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'enrollment_id']);
            $table->index(['module_week_id', 'is_completed']);
            $table->unique(['user_id', 'module_week_id']);  // one record per learner per week
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('week_progress');
    }
};