<?php

declare(strict_types=1);

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
        Schema::create('conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Core identification
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();

            // Condition definition
            $table->string('type'); // discount, tax, fee, shipping, etc.
            $table->string('target'); // subtotal, total, item, price
            $table->string('value'); // e.g., "-10%", "+5", "15"

            // Computed fields (like cart_conditions)
            $table->string('operator')->nullable(); // +, -, *, /, %
            $table->boolean('is_charge')->default(false); // Is this a charge/fee?
            $table->boolean('is_dynamic')->default(false); // Has rules?
            $table->boolean('is_discount')->default(false); // Is this a discount?
            $table->boolean('is_percentage')->default(false); // Percentage-based?
            $table->string('parsed_value')->nullable(); // Parsed numeric value

            // Configuration
            $table->integer('order')->default(0);
            $table->jsonb('attributes')->nullable();
            $table->jsonb('rules')->nullable(); // Dynamic condition rules (if any)

            // Status
            $table->boolean('is_global')->default(false); // Added from separate migration
            $table->boolean('is_active')->default(false);

            $table->timestamps();

            // Indexes for filtering and sorting
            $table->index(['type', 'is_active']);
            $table->index(['target', 'is_active']);
            $table->index('is_charge');
            $table->index('is_discount');
            $table->index('is_percentage');
            $table->index('is_dynamic');
            $table->index('is_global');
            $table->index('order');
        });

        // Add GIN indexes for JSONB columns for efficient querying
        Schema::table('conditions', function (Blueprint $table) {
            $table->rawIndex('attributes', 'conditions_attributes_gin_index', 'gin');
            $table->rawIndex('rules', 'conditions_rules_gin_index', 'gin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conditions');
    }
};
