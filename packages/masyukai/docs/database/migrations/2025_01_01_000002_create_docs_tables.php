<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('view_name');
            $table->string('document_type')->default('invoice');
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('is_default');
            $table->index('document_type');
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('document_number')->unique();
            $table->string('document_type')->default('invoice');
            $table->foreignUuid('document_template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->uuidMorphs('documentable');
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

            $table->index('document_type');
            $table->index('status');
            $table->index('issue_date');
            $table->index('due_date');
        });

        Schema::create('document_status_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->string('changed_by')->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_status_histories');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_templates');
    }
};
