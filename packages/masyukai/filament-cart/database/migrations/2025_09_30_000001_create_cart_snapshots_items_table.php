<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_snapshot_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('cart_snapshots')->onDelete('cascade');
            $table->string('item_id')->index(); // The original cart item ID
            $table->string('name');
            $table->unsignedInteger('price'); // Price in cents (from Money object)
            $table->unsignedInteger('quantity');
            $table->jsonb('attributes')->nullable();
            $table->jsonb('conditions')->nullable();
            $table->string('associated_model')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['cart_id', 'item_id']);
            $table->index('name');
            $table->index('price');
            $table->index('quantity');
            $table->index('associated_model');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_snapshot_items');
    }
};
