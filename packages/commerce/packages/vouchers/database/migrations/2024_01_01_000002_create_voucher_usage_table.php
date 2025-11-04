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
            $table->uuid('id')->primary();
            $table->foreignId('voucher_id')->constrained(
                config('vouchers.table_names.vouchers', 'vouchers')
            )->cascadeOnDelete();
            $table->bigInteger('discount_amount'); // stored in cents
            $table->string('currency', 3);
            $table->string('channel')->nullable();
            $table->nullableUuidMorphs('redeemed_by');
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('used_at');

            // Indexes
            $table->index('voucher_id');
            $table->index('channel');
            $table->index('used_at');
        });

        // Add GIN index for JSONB metadata for efficient querying
        $tableName = config('vouchers.table_names.voucher_usage', 'voucher_usage');
        Schema::table($tableName, function (Blueprint $table) {
            $table->rawIndex('metadata', 'voucher_usage_metadata_gin_index', 'gin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('vouchers.table_names.voucher_usage', 'voucher_usage'));
    }
};
