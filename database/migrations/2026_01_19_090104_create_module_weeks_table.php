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
        Schema::create('module_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_module_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // e.g., "Week 1: Introduction to Data"
            $table->text('description')->nullable();
            $table->integer('week_number'); // Global week number in program (1-8)
            $table->integer('order')->default(0); // Order within module
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('learning_outcomes')->nullable(); // What learners will achieve this week
            $table->boolean('has_assessment')->default(false);
            $table->integer('assessment_pass_percentage')->default(70); // Minimum % to pass
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['program_module_id', 'order']);
            $table->index('week_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_weeks');
    }
};