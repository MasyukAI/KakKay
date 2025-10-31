<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('vouchers.table_names.vouchers', 'vouchers'), function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('owner');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Discount configuration
            $table->string('type'); // percentage, fixed, free_shipping
            $table->bigInteger('value'); // For percentage: store as basis points (e.g., 10.50% = 1050). For fixed: store as cents
            $table->string('currency', 3)->default('MYR');

            // Constraints (in cents)
            $table->bigInteger('min_cart_value')->nullable();
            $table->bigInteger('max_discount')->nullable();

            // Usage limits
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('times_used')->default(0);
            $table->boolean('allows_manual_redemption')->default(false);

            // Validity period
            $table->datetime('starts_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->string('status')->default('active'); // active, paused, expired, depleted

            // Targeting
            $table->jsonb('applicable_products')->nullable();
            $table->jsonb('excluded_products')->nullable();
            $table->jsonb('applicable_categories')->nullable();

            // Metadata
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('status');
            $table->index(['starts_at', 'expires_at']);
        });

        // Add GIN indexes for JSONB columns for efficient querying
        $tableName = config('vouchers.table_names.vouchers', 'vouchers');
        Schema::table($tableName, function (Blueprint $table) {
            $table->rawIndex('applicable_products', 'vouchers_applicable_products_gin_index', 'gin');
            $table->rawIndex('excluded_products', 'vouchers_excluded_products_gin_index', 'gin');
            $table->rawIndex('applicable_categories', 'vouchers_applicable_categories_gin_index', 'gin');
            $table->rawIndex('metadata', 'vouchers_metadata_gin_index', 'gin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('vouchers.table_names.vouchers', 'vouchers'));
    }
};
