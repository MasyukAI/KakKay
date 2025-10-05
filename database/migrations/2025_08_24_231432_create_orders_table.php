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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->uuid('user_id')->nullable();
            $table->uuid('address_id')->nullable();

            // Cart data snapshot
            $table->jsonb('cart_items')->nullable();
            $table->string('delivery_method')->nullable();
            $table->jsonb('checkout_form_data')->nullable(); // Full form backup

            $table->string('status')->default('pending');
            $table->integer('total')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');

            // Indexes
            $table->index('user_id');
            $table->index('address_id');
            $table->index(['user_id', 'status']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');

            // Who/what changed it
            $table->string('actor_type')->default('system');
            $table->uuid('changed_by')->nullable(); // only if actor_type = 'user'
            $table->json('meta')->nullable(); // e.g. job id, webhook payload, gateway event

            $table->text('note')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['order_id', 'to_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_status_histories');
    }
};
