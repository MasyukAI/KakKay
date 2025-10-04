<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->json('client'); // Full client object from API

            // Purchase details - stored as JSON per API structure
            $table->json('purchase'); // Full purchase object from API

            // Core identifiers - all UUIDs as per API
            $table->uuid('brand_id');
            $table->uuid('company_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('billing_template_id')->nullable();
            $table->uuid('client_id')->nullable();

            // Payment details - stored as JSON when present
            $table->json('payment')->nullable(); // Full payment object from API

            // Additional API objects
            $table->json('issuer_details'); // Company/brand details
            $table->json('transaction_data'); // Payment method specific data
            $table->json('status_history'); // Status change tracking

            // Status and workflow - All official CHIP purchase statuses
            $table->enum('status', [
                'created', 'sent', 'viewed', 'pending_execute', 'pending_charge',
                'hold', 'pending_capture', 'pending_release', 'preauthorized',
                'paid', 'paid_authorized', 'recurring_successful', 'cleared', 'settled',
                'pending_refund', 'refunded', 'error', 'blocked', 'cancelled',
                'overdue', 'expired', 'released', 'chargeback',
                'attempted_capture', 'attempted_refund', 'attempted_recurring',
            ])->default('created');

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
            $table->enum('refund_availability', [
                'all', 'full_only', 'partial_only', 'pis_all', 'pis_partial', 'none',
            ])->default('all');
            $table->integer('refundable_amount')->default(0);

            // Currency conversion data
            $table->json('currency_conversion')->nullable();

            // Payment methods and restrictions
            $table->json('payment_method_whitelist')->nullable();

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
            $table->enum('platform', ['web', 'api', 'ios', 'android', 'macos', 'windows'])
                ->default('api');
            $table->enum('product', [
                'purchases', 'billing_invoices', 'billing_subscriptions',
                'billing_subscriptions_invoice',
            ])->default('purchases');
            $table->string('created_from_ip')->nullable();

            // Additional flags
            $table->boolean('marked_as_paid')->default(false);
            $table->string('order_id')->nullable();

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
    }

    public function down(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::dropIfExists($tablePrefix.'purchases');
    }
};
