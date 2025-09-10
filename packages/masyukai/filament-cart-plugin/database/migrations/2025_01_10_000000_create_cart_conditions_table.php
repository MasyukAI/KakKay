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
        Schema::create('cart_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', ['static', 'dynamic'])->default('static');
            $table->enum('target', ['item', 'subtotal', 'total'])->default('subtotal');
            $table->string('value'); // e.g., '+10', '-15%', '*1.5'
            $table->json('attributes')->nullable(); // Additional condition attributes
            $table->integer('order')->default(0);
            $table->boolean('is_global')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['target', 'is_active']);
            $table->index(['is_global', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_conditions');
    }
};