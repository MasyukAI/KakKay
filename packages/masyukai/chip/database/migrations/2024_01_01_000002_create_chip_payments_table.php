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

        Schema::connection($connection)->create($tablePrefix . 'payments', function (Blueprint $table) use ($tablePrefix) {
            $table->id();
            $table->uuid('chip_id')->unique();
            $table->string('purchase_chip_id');
            $table->string('payment_type');
            $table->boolean('is_outgoing')->default(false);
            $table->integer('amount_cents');
            $table->integer('net_amount_cents');
            $table->integer('fee_amount_cents');
            $table->string('currency', 3)->default('MYR');
            $table->string('description')->nullable();
            $table->json('client_details');
            $table->json('transaction_data')->nullable();
            $table->boolean('is_test')->default(false);
            $table->string('reference')->nullable();
            $table->string('reference_generated')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('chip_created_at');
            $table->timestamp('chip_updated_at');
            $table->timestamp('chip_paid_at')->nullable();
            $table->timestamps();

            $table->index(['purchase_chip_id', 'is_test']);
            $table->index(['payment_type', 'is_test']);
            $table->index('chip_created_at');

            $table->foreign('purchase_chip_id')
                ->references('chip_id')
                ->on($tablePrefix . 'purchases')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');
        $connection = config('chip.database.connection');

        Schema::connection($connection)->dropIfExists($tablePrefix . 'payments');
    }
};
