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
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('overview')->nullable();
            $table->string('duration'); // e.g., "12 Weeks", "3 Months"
            $table->decimal('price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0); // For one-time payment discount
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->json('features')->nullable(); // Array of program features
            $table->json('requirements')->nullable(); // Prerequisites
            $table->integer('max_students')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};