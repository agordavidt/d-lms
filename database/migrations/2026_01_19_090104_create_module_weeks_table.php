<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('module_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('week_number');   
            $table->integer('order')->default(0);
            $table->boolean('has_assessment')->default(false);
            $table->integer('assessment_pass_percentage')->default(75);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_module_id', 'order']);
            $table->index('week_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_weeks');
    }
};