<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('week_progress', function (Blueprint $table) {
            $table->decimal('assessment_score', 5, 2)->nullable()->after('completed_at');
            $table->boolean('assessment_passed')->default(false)->after('assessment_score');
            $table->integer('assessment_attempts')->default(0)->after('assessment_passed');
            $table->timestamp('last_assessment_at')->nullable()->after('assessment_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('week_progress', function (Blueprint $table) {
            $table->dropColumn([
                'assessment_score',
                'assessment_passed',
                'assessment_attempts',
                'last_assessment_at'
            ]);
        });
    }
};