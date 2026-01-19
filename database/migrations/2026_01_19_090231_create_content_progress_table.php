<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('content_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('week_content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->integer('progress_percentage')->default(0); // For video watch progress
            $table->integer('time_spent_seconds')->default(0); // Track engagement
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('view_count')->default(0); // How many times accessed
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'enrollment_id']);
            $table->index(['week_content_id', 'is_completed']);
            $table->unique(['user_id', 'week_content_id']); // One progress record per user per content
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_progress');
    }
};