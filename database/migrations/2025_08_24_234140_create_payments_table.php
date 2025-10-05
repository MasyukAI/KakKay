<?php

declare(strict_types=1);

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
            $table->uuid('id')->primary();
            $table->uuid('order_id');

            // Gateway transaction identifiers
            $table->string('gateway_transaction_id')->nullable(); // Gateway's transaction ID
            $table->string('gateway_payment_id')->nullable(); // Gateway's payment/purchase ID
            $table->jsonb('gateway_response')->nullable(); // Store full gateway response

            // Payment details
            $table->integer('amount')->default(0);
            $table->string('status')->default('pending');
            $table->string('method')->nullable();
            $table->string('currency', 3)->default('MYR');

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Additional info
            $table->text('note')->nullable();
            $table->string('reference')->nullable(); // Internal reference
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            // Indexes for performance
            $table->index(['order_id', 'status']);
            $table->index('gateway_transaction_id');
            $table->index('gateway_payment_id');
            $table->index(['status', 'paid_at']);
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
