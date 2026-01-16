<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id')->unique();
            $table->string('reference')->unique(); // Flutterwave reference
            
            // Payment details
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            
            // Payment method and status
            $table->string('payment_method')->nullable(); // card, bank_transfer, ussd
            $table->enum('status', ['pending', 'successful', 'failed', 'cancelled'])->default('pending');
            
            // Payment plan (one-time or installment)
            $table->enum('payment_plan', ['one-time', 'installment'])->default('one-time');
            $table->integer('installment_number')->nullable(); // 1 or 2
            $table->decimal('remaining_balance', 10, 2)->default(0);
            $table->enum('installment_status', ['pending', 'partial', 'completed'])->nullable();
            
            // Flutterwave response data
            $table->json('metadata')->nullable();
            $table->text('flutterwave_response')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['enrollment_id', 'status']);
            $table->index('transaction_id');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};