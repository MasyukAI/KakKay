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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->morphs('shippable'); // polymorphic relation to orders, carts, etc.
            $table->string('provider'); // shipping provider (local, fedex, etc.)
            $table->string('method'); // shipping method (standard, express, etc.)
            $table->string('tracking_number')->nullable()->unique();
            $table->string('status')->default('created'); // created, dispatched, in_transit, delivered, failed
            $table->json('origin_address')->nullable();
            $table->json('destination_address');
            $table->integer('weight')->nullable(); // in grams
            $table->json('dimensions')->nullable(); // length, width, height in cm
            $table->integer('cost'); // cost in cents
            $table->string('currency', 3)->default('MYR');
            $table->json('metadata')->nullable(); // additional provider-specific data
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['tracking_number']);
            $table->index(['shippable_type', 'shippable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};