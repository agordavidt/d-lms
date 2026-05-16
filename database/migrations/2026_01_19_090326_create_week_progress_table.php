<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('week_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();

            // Content completion
            $table->boolean('is_unlocked')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->integer('contents_completed')->default(0);
            $table->integer('total_contents')->default(0);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Weekly assessment gate
            // assessment_passed must be true (score = 100%) before week is completable
            $table->boolean('assessment_passed')->default(false);
            $table->integer('assessment_attempts')->default(0);
            $table->timestamp('last_assessment_at')->nullable();

            $table->timestamps();

            // One progress record per learner per week per enrollment
            $table->unique(['user_id', 'module_week_id', 'enrollment_id']);
            $table->index(['user_id', 'enrollment_id']);
            $table->index(['module_week_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('week_progress');
    }
};