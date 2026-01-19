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
        Schema::create('program_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // e.g., "Module 1: Foundations of Data Analytics"
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // Order of modules in program
            $table->integer('duration_weeks'); // How many weeks this module takes
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('learning_objectives')->nullable(); // Array of learning objectives
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['program_id', 'order']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_modules');
    }
};