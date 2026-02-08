<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Graduation workflow fields
            $table->enum('graduation_status', ['active', 'pending_review', 'graduated', 'dropped'])
                  ->default('active')
                  ->after('status');
            
            // Grading fields
            $table->decimal('final_grade_avg', 5, 2)->nullable()->after('graduation_status');
            $table->decimal('weekly_assessment_avg', 5, 2)->nullable()->after('final_grade_avg');
            $table->decimal('periodic_assessment_avg', 5, 2)->nullable()->after('weekly_assessment_avg');
            
            // Graduation timestamps
            $table->timestamp('graduation_requested_at')->nullable()->after('periodic_assessment_avg');
            $table->timestamp('graduation_approved_at')->nullable()->after('graduation_requested_at');
            $table->unsignedBigInteger('approved_by')->nullable()->after('graduation_approved_at');
            
            // Certificate tracking
            $table->string('certificate_key')->unique()->nullable()->after('approved_by');
            $table->timestamp('certificate_issued_at')->nullable()->after('certificate_key');
            
            // Foreign key for approver
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'graduation_status',
                'final_grade_avg',
                'weekly_assessment_avg',
                'periodic_assessment_avg',
                'graduation_requested_at',
                'graduation_approved_at',
                'approved_by',
                'certificate_key',
                'certificate_issued_at',
            ]);
        });
    }
};