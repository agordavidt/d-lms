<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('status', ['ongoing', 'completed', 'cancelled'])->default('ongoing');
            $table->integer('enrolled_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['program_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};