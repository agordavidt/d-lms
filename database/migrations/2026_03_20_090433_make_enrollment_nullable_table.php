<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['cohort_id']);
            $table->unsignedBigInteger('cohort_id')->nullable()->change();
            $table->foreign('cohort_id')->references('id')->on('cohorts')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id')->nullable(false)->change();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['cohort_id']);
            $table->unsignedBigInteger('cohort_id')->nullable(false)->change();
            $table->foreign('cohort_id')->references('id')->on('cohorts')->cascadeOnDelete();
        });
    }
};