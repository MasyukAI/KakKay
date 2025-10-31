<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('vouchers.table_names.voucher_usage', 'voucher_usage'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained(
                config('vouchers.table_names.vouchers', 'vouchers')
            )->cascadeOnDelete();
            $table->string('user_identifier'); // user_id or session_id
            $table->string('cart_identifier')->nullable();
            $table->decimal('discount_amount', 10, 2);
            $table->string('currency', 3);
            $table->jsonb('cart_snapshot')->nullable();
            $table->string('channel')->default('automatic');
            $table->nullableMorphs('redeemed_by');
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('used_at');

            // Indexes
            $table->index('voucher_id');
            $table->index('user_identifier');
            $table->index('channel');
            $table->index('used_at');
        });

        // Add GIN indexes for JSONB columns for efficient querying
        $tableName = config('vouchers.table_names.voucher_usage', 'voucher_usage');
        Schema::table($tableName, function (Blueprint $table) {
            $table->rawIndex('cart_snapshot', 'voucher_usage_cart_snapshot_gin_index', 'gin');
            $table->rawIndex('metadata', 'voucher_usage_metadata_gin_index', 'gin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('vouchers.table_names.voucher_usage', 'voucher_usage'));
    }
};
