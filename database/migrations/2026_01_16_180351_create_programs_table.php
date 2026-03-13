<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();

            // Ownership
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();

            // Core info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');         
            $table->string('cover_image')->nullable();
            $table->string('duration');            

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            // Graduation requirement
            $table->decimal('min_passing_average', 5, 2)->default(70.00);
            // Status flow: draft → under_review → active → inactive
            $table->enum('status', ['draft', 'under_review', 'active', 'inactive'])->default('draft');
            $table->timestamp('submitted_at')->nullable();   
            $table->timestamp('reviewed_at')->nullable();    
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();        // Admin feedback to mentor

            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index('mentor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};