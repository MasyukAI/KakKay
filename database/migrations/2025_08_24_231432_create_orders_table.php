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
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');

            // Cart data snapshot
            $table->json('cart_items')->nullable();
            $table->string('delivery_method')->nullable();
            $table->json('checkout_form_data')->nullable(); // Full form backup

            $table->string('status')->default('pending');
            $table->integer('total')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('address_id');
            $table->index(['user_id', 'status']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('from_status')->nullable();
            $table->string('to_status');

            // Who/what changed it
            $table->string('actor_type')->default('system');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); // only if actor_type = 'user'
            $table->json('meta')->nullable(); // e.g. job id, webhook payload, gateway event

            $table->text('note')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

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
