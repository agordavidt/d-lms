<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->integer('time_limit_minutes')->nullable();  
            $table->integer('pass_percentage')->default(70);
            $table->boolean('randomize_questions')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique('module_week_id');  
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};