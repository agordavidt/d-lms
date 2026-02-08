<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->decimal('min_passing_average', 5, 2)->default(70.00)->after('duration');
            $table->boolean('require_all_assessments')->default(true)->after('min_passing_average');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['min_passing_average', 'require_all_assessments']);
        });
    }
};