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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity');
            $table->enum('type', ['in', 'out']);
            $table->string('note')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamps();
            $table->index(['product_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
