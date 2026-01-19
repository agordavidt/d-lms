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
        Schema::create('week_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_unlocked')->default(false); // Week accessibility
            $table->boolean('is_completed')->default(false);
            $table->integer('progress_percentage')->default(0); // % of required content completed
            $table->integer('contents_completed')->default(0);
            $table->integer('total_contents')->default(0);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'enrollment_id']);
            $table->index(['module_week_id', 'is_completed']);
            $table->unique(['user_id', 'module_week_id']); // One progress record per user per week
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('week_progress');
    }
};