<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->create($tablePrefix . 'purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('chip_id')->unique();
            $table->string('status');
            $table->json('client_details');
            $table->json('purchase_details');
            $table->string('brand_id');
            $table->json('payment_details')->nullable();
            $table->string('checkout_url')->nullable();
            $table->string('invoice_url')->nullable();
            $table->string('reference')->nullable();
            $table->string('reference_generated')->nullable();
            $table->boolean('is_test')->default(false);
            $table->boolean('is_recurring_token')->default(false);
            $table->string('recurring_token')->nullable();
            $table->integer('amount_cents');
            $table->string('currency', 3)->default('MYR');
            $table->json('metadata')->nullable();
            $table->timestamp('chip_created_at');
            $table->timestamp('chip_updated_at');
            $table->timestamps();

            $table->index(['status', 'is_test']);
            $table->index(['brand_id', 'is_test']);
            $table->index('chip_created_at');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->dropIfExists($tablePrefix . 'purchases');
    }
};
