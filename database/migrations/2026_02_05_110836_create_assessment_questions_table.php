<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->enum('question_type', ['multiple_choice', 'true_false', 'multiple_select'])->default('multiple_choice');
            $table->text('question_text');
            $table->string('question_image')->nullable();
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->text('explanation')->nullable();
            $table->json('options'); // Store answer choices
            $table->json('correct_answer'); // Store correct answer(s)
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['assessment_id', 'order']);
            $table->index('question_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};