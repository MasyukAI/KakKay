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
        Schema::create('digital_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('order_item_id')->nullable(); // per-item control
            $table->string('asset_path');                // storage path (private disk) or key
            $table->string('delivery_token')->unique();  // used in signed routes
            $table->unsignedInteger('max_downloads')->default(5);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // optional expiry
            $table->enum('status', ['pending', 'available', 'expired', 'revoked'])->default('available');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_deliveries');
    }
};
