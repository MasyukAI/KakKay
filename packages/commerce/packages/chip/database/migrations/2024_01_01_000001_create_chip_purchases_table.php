<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::create($tablePrefix.'purchases', function (Blueprint $table) {
            // Core API fields - exact match with CHIP API
            $table->uuid('id')->primary();
            $table->string('type')->default('purchase');
            $table->integer('created_on'); // Unix timestamp as per API
            $table->integer('updated_on'); // Unix timestamp as per API

            // Client details - stored as JSON per API structure
            $table->jsonb('client'); // Full client object from API

            // Purchase details - stored as JSON per API structure
            $table->jsonb('purchase'); // Full purchase object from API

            // Core identifiers - all UUIDs as per API
            $table->uuid('brand_id');
            $table->uuid('company_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('billing_template_id')->nullable();
            $table->uuid('client_id')->nullable();

            // Payment details - stored as JSON when present
            $table->jsonb('payment')->nullable(); // Full payment object from API

            // Additional API objects
            $table->jsonb('issuer_details'); // Company/brand details
            $table->jsonb('transaction_data'); // Payment method specific data
            $table->jsonb('status_history'); // Status change tracking

            // Status and workflow - All official CHIP purchase statuses
            $table->string('status', 32)
                ->default('created')
                ->comment('Backed by AIArmada\\Chip\\Enums\\PurchaseStatus enum.');

            // Timestamps
            $table->integer('viewed_on')->nullable();

            // Configuration flags
            $table->boolean('send_receipt')->default(false);
            $table->boolean('is_test')->default(false);
            $table->boolean('is_recurring_token')->default(false);
            $table->uuid('recurring_token')->nullable();
            $table->boolean('skip_capture')->default(false);
            $table->boolean('force_recurring')->default(false);

            // Invoice and reference fields
            $table->string('reference', 128)->nullable();
            $table->string('reference_generated')->nullable();
            $table->text('notes')->nullable();
            $table->string('issued')->nullable(); // ISO 8601 date format
            $table->integer('due')->nullable(); // Unix timestamp

            // Refund information
            $table->string('refund_availability', 32)
                ->default('all')
                ->comment('Backed by CHIP refund availability values.');
            $table->integer('refundable_amount')->default(0);

            // Currency conversion data
            $table->jsonb('currency_conversion')->nullable();

            // Payment methods and restrictions
            $table->jsonb('payment_method_whitelist')->nullable();

            // URLs and redirects
            $table->string('success_redirect', 500)->nullable();
            $table->string('failure_redirect', 500)->nullable();
            $table->string('cancel_redirect', 500)->nullable();
            $table->string('success_callback', 500)->nullable();
            $table->string('invoice_url', 500)->nullable();
            $table->string('checkout_url', 500)->nullable();
            $table->string('direct_post_url', 500)->nullable();

            // Platform and creation details
            $table->string('creator_agent', 32)->nullable();
            $table->string('platform', 16)
                ->default('api')
                ->comment('Expected values: web, api, ios, android, macos, windows.');
            $table->string('product', 48)
                ->default('purchases')
                ->comment('Expected values: purchases, billing_invoices, billing_subscriptions, billing_subscriptions_invoice.');
            $table->string('created_from_ip')->nullable();

            // Additional flags
            $table->boolean('marked_as_paid')->default(false);
            $table->string('order_id')->nullable();

            // Metadata for additional application-specific data
            $table->jsonb('metadata')->nullable();

            // Laravel timestamps for internal use
            $table->timestamps();

            // Indexes for optimal query performance
            $table->index(['status', 'is_test']);
            $table->index(['brand_id', 'is_test']);
            $table->index(['company_id', 'is_test']);
            $table->index('created_on');
            $table->index('viewed_on');
            $table->index('due');
        });

        // Add GIN index for JSONB metadata column for efficient querying
        Schema::table($tablePrefix.'purchases', function (Blueprint $table) {
            $table->rawIndex('metadata', 'chip_purchases_metadata_gin_index', 'gin');
        });

        // Add optimized expression indexes for cart_id lookups (faster than GIN for equality)
        DB::statement("
            CREATE INDEX chip_purchases_metadata_cart_id_idx 
            ON {$tablePrefix}purchases ((metadata->>'cart_id'))
        ");

        DB::statement("
            CREATE INDEX chip_purchases_status_cart_id_idx 
            ON {$tablePrefix}purchases (status, ((metadata->>'cart_id')))
        ");
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::dropIfExists($tablePrefix.'purchases');
    }
};
