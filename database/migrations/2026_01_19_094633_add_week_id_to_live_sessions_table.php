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
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->foreignId('week_id')->nullable()->after('cohort_id')
                ->constrained('module_weeks')->nullOnDelete();
            
            $table->index('week_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropForeign(['week_id']);
            $table->dropColumn('week_id');
        });
    }
};