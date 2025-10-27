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
            $table->decimal('value', 10, 2);
            $table->string('currency', 3)->default('MYR');

            // Constraints
            $table->decimal('min_cart_value', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();

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
            $table->json('applicable_products')->nullable();
            $table->json('excluded_products')->nullable();
            $table->json('applicable_categories')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code');
            $table->index('status');
            $table->index(['starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('vouchers.table_names.vouchers', 'vouchers'));
    }
};
