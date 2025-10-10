# Docs Package for Laravel

Modern Laravel package for generating professional documents (invoices, receipts, and more) with PDF support using Spatie Laravel PDF and Tailwind CSS.

## Features

- 📄 Generate professional PDF documents with Blade templates
- 🎨 Tailwind CSS support for beautiful, customizable designs
- 📊 Support for multiple document types: invoices, receipts (expandable)
- 🔢 Automatic document numbering with configurable formats
- 📝 Multiple templates support for each document type
- 📱 Status tracking (Draft, Pending, Sent, Paid, etc.)
- 💾 Store PDFs on any Laravel filesystem disk
- 📧 Email document capabilities
- 🔄 Status history tracking
- 🏢 Configurable company and customer data

## Supported Document Types

### Currently Supported
- **Invoices** - Full invoice generation with line items, taxes, discounts
- **Receipts** - Payment receipts (coming soon)

### Expandable Architecture
The package is designed to easily support additional document types such as quotations, purchase orders, delivery notes, and more.

## Installation

Install the package via Composer:

```bash
composer require masyukai/docs
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=docs-config
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The package configuration is located in `config/docs.php`. You can customize:

- Document type configurations (invoice, receipt)
- Document number formats per type
- Default templates per type
- PDF storage settings
- Company information
- Currency and tax defaults

## Usage

### Using the Generic Document Service (Recommended)

The package now provides generic `Document` classes that can handle any document type:

```php
use MasyukAI\Docs\Facades\Document;
use MasyukAI\Docs\DataObjects\DocumentData;
use MasyukAI\Docs\Enums\DocumentStatus;

// Create an invoice
$document = Document::createDocument(DocumentData::from([
    'document_type' => 'invoice',
    'items' => [
        [
            'name' => 'Web Development Service',
            'quantity' => 1,
            'price' => 2500.00,
        ],
    ],
    'customer_data' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ],
    'generate_pdf' => true,
]));

// Update status
Document::updateDocumentStatus($document, DocumentStatus::PAID);

// Generate PDF
$pdfUrl = Document::generatePdf($document);
```

### Creating an Invoice (Backward Compatible)

For backward compatibility, you can still use the `Invoice` facade which wraps the new `Document` classes:

```php
use MasyukAI\Docs\Facades\Invoice;
use MasyukAI\Docs\DataObjects\InvoiceData;

$invoice = Invoice::createInvoice(InvoiceData::from([
    'items' => [
        [
            'name' => 'Web Development Service',
            'description' => 'Custom website development',
            'quantity' => 1,
            'price' => 2500.00,
        ],
        [
            'name' => 'Hosting (Annual)',
            'quantity' => 1,
            'price' => 500.00,
        ],
    ],
    'customer_data' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'address' => '123 Main St',
        'city' => 'Kuala Lumpur',
        'state' => 'Federal Territory',
        'postal_code' => '50000',
        'country' => 'Malaysia',
    ],
    'notes' => 'Thank you for your business!',
    'terms' => 'Payment due within 30 days.',
    'generate_pdf' => true,
]));
```

### Generating PDF

```php
// Generate and save PDF
$pdfUrl = Invoice::generatePdf($invoice, save: true);

// Generate PDF without saving
$pdfContent = Invoice::generatePdf($invoice, save: false);
```

### Download Invoice PDF

```php
$pdfUrl = Invoice::downloadPdf($invoice);
```

### Update Invoice Status

```php
use MasyukAI\Docs\Enums\InvoiceStatus;

Invoice::updateInvoiceStatus($invoice, InvoiceStatus::PAID, 'Payment received via bank transfer');
```

### Mark Invoice as Paid

```php
$invoice->markAsPaid();
```

### Mark Invoice as Sent

```php
$invoice->markAsSent();
```

### Email Invoice

```php
Invoice::emailInvoice($invoice, 'customer@example.com');
```

### Link Invoice to a Model

You can link invoices to any model using polymorphic relationships:

```php
$invoice = Invoice::createInvoice(InvoiceData::from([
    'invoiceable_type' => 'App\\Models\\Order',
    'invoiceable_id' => $order->id,
    // ... other data
]));

// Access the linked model
$order = $invoice->invoiceable;
```

## Creating Custom Templates

1. Create a new Blade view in `resources/views/vendor/docs/templates/`:

```blade
<!-- resources/views/vendor/docs/templates/modern.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- Your custom design here -->
    <div class="container">
        <h1>{{ $invoice->invoice_number }}</h1>
        <!-- Add your custom layout -->
    </div>
</body>
</html>
```

2. Create a template record:

```php
use MasyukAI\Docs\Models\InvoiceTemplate;

InvoiceTemplate::create([
    'name' => 'Modern Template',
    'slug' => 'modern',
    'description' => 'A modern, clean invoice template',
    'view_name' => 'modern',
    'is_default' => false,
]);
```

3. Use the template:

```php
$invoice = Invoice::createInvoice(InvoiceData::from([
    'template_slug' => 'modern',
    // ... other data
]));
```

For more information on using Tailwind CSS with invoice templates, see [docs/TAILWIND_USAGE.md](docs/TAILWIND_USAGE.md).

## Invoice Status

Available invoice statuses:

- `DRAFT` - Invoice is being prepared
- `PENDING` - Invoice is ready to be sent
- `SENT` - Invoice has been sent to customer
- `PAID` - Invoice has been paid
- `PARTIALLY_PAID` - Partial payment received
- `OVERDUE` - Invoice is past due date
- `CANCELLED` - Invoice has been cancelled
- `REFUNDED` - Invoice has been refunded

## Requirements

- PHP 8.3+
- Laravel 12.0+
- Spatie Laravel PDF 1.5+

## Migration from Invoice-only Package

The package has been refactored from an invoice-specific package to a generic document package. All old `Invoice*` classes are still available for backward compatibility and now internally use the new `Document*` classes.

### What Changed

1. **New Generic Classes:**
   - `Document` model (replaces direct use of `Invoice` table)
   - `DocumentTemplate` model (supports multiple document types)
   - `DocumentService` (generic document operations)
   - `DocumentStatus` enum
   - `DocumentData` DTO

2. **Database Structure:**
   - `documents` table (with `document_type` field for invoices, receipts, etc.)
   - `document_templates` table (with `document_type` field)
   - `document_status_histories` table

3. **Backward Compatibility:**
   - All `Invoice*` classes still work and wrap the new `Document*` classes
   - Old field names like `invoice_number`, `invoiceable_type` are supported
   - Existing code will continue to work without changes

### Migration Guide

**Option 1: Gradual Migration (Recommended)**
```php
// Old code still works
use MasyukAI\Docs\Facades\Invoice;
$invoice = Invoice::createInvoice($data);

// New code for new features
use MasyukAI\Docs\Facades\Document;
$document = Document::createDocument($data);
```

**Option 2: Full Migration**
```php
// Replace
use MasyukAI\Docs\Models\Invoice;
use MasyukAI\Docs\DataObjects\InvoiceData;

// With
use MasyukAI\Docs\Models\Document;
use MasyukAI\Docs\DataObjects\DocumentData;
```

## Development Tools

The package includes the following development tools as specified:

- **Spatie Laravel Package Tools** - For package scaffolding and structure
- **Larastan** - PHPStan wrapper for Laravel, level 6 static analysis
- **Laravel Rector** - Automated code refactoring and upgrades
- **Laravel Pint** - Opinionated PHP code style fixer

Run these tools with:

```bash
composer format    # Run Laravel Pint
composer analyse   # Run Larastan
vendor/bin/rector  # Run Rector
```

## License

MIT License. See [LICENSE](LICENSE.md) for details.
