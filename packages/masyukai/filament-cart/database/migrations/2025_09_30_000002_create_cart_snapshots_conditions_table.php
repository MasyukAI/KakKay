<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_snapshot_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('cart_snapshots')->onDelete('cascade');
            $table->foreignUuid('cart_item_id')->nullable()->constrained('cart_snapshot_items')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // discount, tax, fee, shipping, etc.
            $table->string('target'); // subtotal, total, price, etc.
            $table->string('value'); // percentage or fixed amount
            $table->string('operator')->nullable(); // +, -, *, /, %
            $table->boolean('is_charge')->default(false);
            $table->boolean('is_dynamic')->default(false);
            $table->boolean('is_discount')->default(false);
            $table->boolean('is_percentage')->default(false);
            $table->boolean('is_global')->default(false);
            $table->string('parsed_value')->nullable(); // Calculated value
            $table->jsonb('rules')->nullable(); // Additional rules
            $table->integer('order')->default(0);
            $table->jsonb('attributes')->nullable();
            $table->string('item_id')->nullable()->index(); // Cart item ID this applies to (if item-level)
            $table->timestamps();

            // Indexes for performance
            $table->index(['cart_id', 'name']);
            $table->index('name');
            $table->index('type');
            $table->index('target');
            $table->index('order');
            $table->index('is_discount');
            $table->index('is_charge');
            $table->index('is_percentage');
            $table->index('is_dynamic');
            $table->index('is_global');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_snapshot_conditions');
    }
};
