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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent','fixed']); // percent = basis points (e.g. 1500 = 15%), fixed = cents
            $table->integer('value'); // see type note above
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('times_used')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active','ends_at']);
        });

        // Which discounts applied to which orders (with resolved applied amount)
        Schema::create('order_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('discount_id')->constrained()->onDelete('restrict');
            $table->integer('amount_applied')->default(0); // cents actually deducted
            $table->timestamps();
            $table->unique(['order_id','discount_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
