# Migration Guide: Invoice Package → Docs Package

## Overview

The `masyukai/docs` package has been refactored from an invoice-specific package to a generic document generation package. This guide explains the changes and how to migrate your code.

## What Changed

### Database Structure

#### Old Structure (Invoice-only)
- `invoice_templates` table
- `invoices` table
- `invoice_status_histories` table

#### New Structure (Generic Documents)
- `document_templates` table (with `document_type` field)
- `documents` table (with `document_type` field)
- `document_status_histories` table

**Important:** The old migration file (`2025_01_01_000001_create_invoice_tables.php`) has been removed and replaced with `2025_01_01_000002_create_docs_tables.php`.

### PHP Classes

#### Models

| Old Class | New Class | Status |
|-----------|-----------|--------|
| `Invoice` | `Document` | Old class maintained for backward compatibility |
| `InvoiceTemplate` | `DocumentTemplate` | Old class maintained for backward compatibility |
| `InvoiceStatusHistory` | `DocumentStatusHistory` | Old class maintained for backward compatibility |

#### Services

| Old Class | New Class | Status |
|-----------|-----------|--------|
| `InvoiceService` | `DocumentService` | Old class maintained as wrapper |

#### Data Objects

| Old Class | New Class | Status |
|-----------|-----------|--------|
| `InvoiceData` | `DocumentData` | Old class maintained with conversion method |

#### Enums

| Old Class | New Class | Status |
|-----------|-----------|--------|
| `InvoiceStatus` | `DocumentStatus` | Old class maintained as wrapper |

#### Facades

| Old Class | New Class | Status |
|-----------|-----------|--------|
| `Invoice` | `Document` | Old class maintained |

## Backward Compatibility

All old classes are **100% backward compatible**. Your existing code will continue to work without any changes.

### How It Works

1. **Old Model Classes** extend the new Document models with:
   - Global scopes to filter by `document_type = 'invoice'`
   - Accessor/mutator methods for old field names (e.g., `invoice_number` → `document_number`)
   - Automatic document type setting on creation

2. **Old Service Classes** wrap the new DocumentService and delegate all operations

3. **Old Data Objects** support conversion to new DocumentData format

4. **Old Enums** delegate to new DocumentStatus enum methods

## Migration Strategies

### Strategy 1: No Changes Required (Recommended for Existing Code)

Keep using the old classes. They will continue to work indefinitely:

```php
use MasyukAI\Docs\Facades\Invoice;
use MasyukAI\Docs\DataObjects\InvoiceData;
use MasyukAI\Docs\Enums\InvoiceStatus;

// This continues to work exactly as before
$invoice = Invoice::createInvoice(InvoiceData::from([
    'items' => [...],
    'customer_data' => [...],
]));

Invoice::updateInvoiceStatus($invoice, InvoiceStatus::PAID);
```

### Strategy 2: Gradual Migration (Recommended for New Code)

Use new classes for new features while keeping old code unchanged:

```php
// Old code remains unchanged
use MasyukAI\Docs\Facades\Invoice;
$invoice = Invoice::createInvoice($invoiceData);

// New code uses generic Document classes
use MasyukAI\Docs\Facades\Document;
use MasyukAI\Docs\DataObjects\DocumentData;

$receipt = Document::createDocument(DocumentData::from([
    'document_type' => 'receipt',
    'items' => [...],
]));
```

### Strategy 3: Full Migration (Optional)

Migrate all code to use new generic classes:

#### Before
```php
use MasyukAI\Docs\Models\Invoice;
use MasyukAI\Docs\Models\InvoiceTemplate;
use MasyukAI\Docs\DataObjects\InvoiceData;
use MasyukAI\Docs\Enums\InvoiceStatus;
use MasyukAI\Docs\Facades\Invoice as InvoiceFacade;

$invoice = InvoiceFacade::createInvoice(InvoiceData::from([
    'invoice_number' => 'INV-001',
    'invoiceable_type' => Order::class,
    'invoiceable_id' => $order->id,
    'items' => [...],
]));
```

#### After
```php
use MasyukAI\Docs\Models\Document;
use MasyukAI\Docs\Models\DocumentTemplate;
use MasyukAI\Docs\DataObjects\DocumentData;
use MasyukAI\Docs\Enums\DocumentStatus;
use MasyukAI\Docs\Facades\Document as DocumentFacade;

$document = DocumentFacade::createDocument(DocumentData::from([
    'document_type' => 'invoice',
    'document_number' => 'INV-001',
    'documentable_type' => Order::class,
    'documentable_id' => $order->id,
    'items' => [...],
]));
```

## Field Name Mappings

When using the old `Invoice` model, these field names are automatically converted:

| Old Field Name | New Field Name | Notes |
|----------------|----------------|-------|
| `invoice_number` | `document_number` | Automatic conversion via accessors |
| `invoice_template_id` | `document_template_id` | Automatic conversion via accessors |
| `invoiceable_type` | `documentable_type` | Automatic conversion via accessors |
| `invoiceable_id` | `documentable_id` | Automatic conversion via accessors |
| N/A | `document_type` | Automatically set to 'invoice' |

## Template Variables

Blade templates now support both old and new variable names:

```blade
{{-- Both work --}}
{{ $invoice->invoice_number }}
{{ $document->document_number }}

{{-- Recommended: Use $doc which works for both --}}
@php
    $doc = $document ?? $invoice;
@endphp
{{ $doc->document_number ?? $doc->invoice_number }}
```

## Database Migration

### For Fresh Installations

Just run migrations normally:
```bash
php artisan migrate
```

The new migration will create the generic `documents`, `document_templates`, and `document_status_histories` tables.

### For Existing Installations

If you already have the old invoice tables, you'll need to create a migration to rename them:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename tables
        Schema::rename('invoice_templates', 'document_templates');
        Schema::rename('invoices', 'documents');
        Schema::rename('invoice_status_histories', 'document_status_histories');
        
        // Add document_type column to templates
        Schema::table('document_templates', function (Blueprint $table) {
            $table->string('document_type')->default('invoice')->after('view_name');
            $table->index('document_type');
        });
        
        // Add document_type column to documents
        Schema::table('documents', function (Blueprint $table) {
            $table->string('document_type')->default('invoice')->after('document_number');
            $table->index('document_type');
        });
        
        // Rename columns in documents table
        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('invoice_number', 'document_number');
            $table->renameColumn('invoice_template_id', 'document_template_id');
            $table->renameColumn('invoiceable_type', 'documentable_type');
            $table->renameColumn('invoiceable_id', 'documentable_id');
        });
        
        // Rename column in document_status_histories table
        Schema::table('document_status_histories', function (Blueprint $table) {
            $table->renameColumn('invoice_id', 'document_id');
        });
    }
    
    public function down(): void
    {
        // Reverse all changes
        Schema::table('document_status_histories', function (Blueprint $table) {
            $table->renameColumn('document_id', 'invoice_id');
        });
        
        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('documentable_id', 'invoiceable_id');
            $table->renameColumn('documentable_type', 'invoiceable_type');
            $table->renameColumn('document_template_id', 'invoice_template_id');
            $table->renameColumn('document_number', 'invoice_number');
        });
        
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
        
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
        
        Schema::rename('document_status_histories', 'invoice_status_histories');
        Schema::rename('documents', 'invoices');
        Schema::rename('document_templates', 'invoice_templates');
    }
};
```

## Testing Your Migration

After migration, verify everything works:

```php
// Test old Invoice facade
$invoice = Invoice::createInvoice(InvoiceData::from([...]));
expect($invoice)->toBeInstanceOf(Invoice::class);

// Test new Document facade
$document = Document::createDocument(DocumentData::from([
    'document_type' => 'invoice',
    // ...
]));
expect($document)->toBeInstanceOf(Document::class);

// Test backward compatibility
expect($invoice->invoice_number)->toBe($invoice->document_number);
```

## Benefits of Migration

1. **Support for Multiple Document Types:** Easily add receipts, quotes, purchase orders, etc.
2. **Better Architecture:** Generic classes that can handle any document type
3. **Backward Compatible:** No breaking changes, migrate at your own pace
4. **Future-Proof:** Ready for new document types without major refactoring

## Support

If you encounter any issues during migration, please open an issue on GitHub.

## Deprecation Timeline

The old `Invoice*` classes are not deprecated and will continue to be maintained. However, we recommend using the new `Document*` classes for new code.
