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
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained()->onDelete('cascade'); // per-item control
            $table->string('asset_path');                // storage path (private disk) or key
            $table->string('delivery_token')->unique();  // used in signed routes
            $table->unsignedInteger('max_downloads')->default(5);
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // optional expiry
            $table->enum('status', ['pending', 'available', 'expired', 'revoked'])->default('available');
            $table->timestamps();

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
