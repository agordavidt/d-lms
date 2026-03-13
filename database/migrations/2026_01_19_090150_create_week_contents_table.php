<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('week_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_week_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->enum('content_type', ['video', 'pdf', 'link', 'article']);
            $table->integer('order')->default(0);

            // Content fields — only one used depending on content_type
            $table->string('video_url')->nullable();               
            $table->integer('video_duration_minutes')->nullable();  
            $table->string('file_path')->nullable();               
            $table->string('external_url')->nullable();             
            $table->longText('text_content')->nullable();           

            $table->boolean('is_required')->default(true);
            $table->boolean('is_downloadable')->default(false);    

            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_week_id', 'order']);
            $table->index('content_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('week_contents');
    }
};