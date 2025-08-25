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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->integer('amount')->default(0); // cents
            $table->enum('status', ['pending','completed','failed','refunded'])->default('pending');
            $table->enum('method', [
                'credit_card','debit_card','paypal','bank_transfer','ewallet','cash','cod','stripe'
            ])->nullable();
            $table->string('currency', 3)->default('MYR');
            $table->string('transaction_id')->nullable();
            $table->timestamp('payment_date')->useCurrent();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['order_id','status','method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
