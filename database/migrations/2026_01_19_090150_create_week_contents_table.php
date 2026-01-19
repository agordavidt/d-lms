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
        Schema::create('week_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // Instructor who added it
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', ['video', 'pdf', 'link', 'text']); // Type of content
            $table->integer('order')->default(0); // Order within week
            
            // Content storage fields (only one will be used based on type)
            $table->string('video_url')->nullable(); // For video type (YouTube, Vimeo, etc.)
            $table->integer('video_duration_minutes')->nullable(); // Estimated watch time
            $table->string('file_path')->nullable(); // For PDF uploads
            $table->string('external_url')->nullable(); // For link type
            $table->longText('text_content')->nullable(); // For text/article type (HTML)
            
            $table->boolean('is_required')->default(true); // Must complete to unlock next week
            $table->boolean('is_downloadable')->default(false); // For PDFs
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('metadata')->nullable(); // Additional info (file size, author, etc.)
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['module_week_id', 'order']);
            $table->index('content_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('week_contents');
    }
};