<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->constrained()->cascadeOnDelete();
            $table->string('enrollment_number')->unique();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->date('enrolled_at');
            $table->date('completed_at')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['cohort_id', 'status']);
            $table->unique(['user_id', 'program_id', 'cohort_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};