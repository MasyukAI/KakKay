<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('view_name');
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('is_default');
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->foreignUuid('invoice_template_id')->nullable()->constrained('invoice_templates')->nullOnDelete();
            $table->uuidMorphs('invoiceable');
            $table->string('status')->default('draft');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('customer_data')->nullable();
            $table->json('company_data')->nullable();
            $table->json('items')->nullable();
            $table->json('metadata')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('issue_date');
            $table->index('due_date');
        });

        Schema::create('invoice_status_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->string('changed_by')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_status_histories');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_templates');
    }
};
