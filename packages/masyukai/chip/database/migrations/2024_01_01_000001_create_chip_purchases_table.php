<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('chip.database.table_prefix', 'chip_');

        Schema::create($tablePrefix . 'purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('chip_id')->unique();
            $table->string('type')->default('purchase');
            $table->string('status');
            $table->json('client_details');
            $table->json('purchase_details');
            $table->string('brand_id');
            $table->json('payment_details')->nullable();
            $table->json('issuer_details')->nullable();
            $table->json('transaction_data')->nullable();
            $table->json('status_history')->nullable();
            $table->integer('viewed_on')->nullable();
            $table->string('company_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('billing_template_id')->nullable();
            $table->string('client_id')->nullable();
            $table->boolean('send_receipt')->default(false);
            $table->string('checkout_url')->nullable();
            $table->string('invoice_url')->nullable();
            $table->string('direct_post_url')->nullable();
            $table->string('reference')->nullable();
            $table->string('reference_generated')->nullable();
            $table->text('notes')->nullable();
            $table->string('issued')->nullable();
            $table->integer('due')->nullable();
            $table->string('refund_availability')->default('all');
            $table->integer('refundable_amount')->default(0);
            $table->json('currency_conversion')->nullable();
            $table->json('payment_method_whitelist')->nullable();
            $table->string('success_redirect')->nullable();
            $table->string('failure_redirect')->nullable();
            $table->string('cancel_redirect')->nullable();
            $table->string('success_callback')->nullable();
            $table->string('creator_agent')->nullable();
            $table->string('platform')->default('api');
            $table->string('product')->default('purchases');
            $table->string('created_from_ip')->nullable();
            $table->boolean('marked_as_paid')->default(false);
            $table->string('order_id')->nullable();
            $table->boolean('is_test')->default(false);
            $table->boolean('is_recurring_token')->default(false);
            $table->string('recurring_token')->nullable();
            $table->boolean('skip_capture')->default(false);
            $table->boolean('force_recurring')->default(false);
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

        Schema::dropIfExists($tablePrefix . 'purchases');
    }
};
