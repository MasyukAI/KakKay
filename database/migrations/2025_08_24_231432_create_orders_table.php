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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('cliend_id')->constrained()->onDelete('restrict');

            // snapshot shipping address
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            $table->enum('status', ['pending','processing','shipped','delivered','cancelled','refunded'])->default('pending');

            // Payment handling
            $table->enum('payment_status', ['unpaid','partial','paid','refunded','failed'])->default('unpaid');
            $table->integer('amount_paid')->default(0);     // cents
            $table->integer('amount_refunded')->default(0); // cents

            $table->integer('total_amount')->default(0); // cents
            $table->timestamps();

            $table->index(['client_id','status']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('from_status')->nullable();
            $table->string('to_status');

            // Who/what changed it
            $table->enum('actor_type', ['user','system','webhook','job','gateway'])->default('system');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); // only if actor_type = 'user'
            $table->json('meta')->nullable(); // e.g. job id, webhook payload, gateway event

            $table->text('note')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index(['order_id','to_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
