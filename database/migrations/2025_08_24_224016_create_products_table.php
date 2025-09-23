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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('description')->nullable();
            $table->uuid('category_id');
            $table->integer('price')->default(0);

            // Shipping and physical properties
            $table->integer('weight')->default(0); // Weight in grams
            $table->integer('length')->nullable(); // Length in mm
            $table->integer('width')->nullable(); // Width in mm
            $table->integer('height')->nullable(); // Height in mm
            $table->boolean('is_digital')->default(false); // Digital products (e-books, software, etc.)
            $table->boolean('free_shipping')->default(false); // Override shipping calculation

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
