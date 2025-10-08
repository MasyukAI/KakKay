# Invoice Package for Laravel

Modern Laravel package for generating invoices with PDF support using Spatie Laravel PDF and Tailwind CSS.

## Features

- 📄 Generate professional PDF invoices with Blade templates
- 🎨 Tailwind CSS support for beautiful, customizable invoice designs
- 📊 Dynamic invoice data with flexible line items
- 🔢 Automatic invoice numbering with configurable format
- 📝 Multiple invoice templates support
- 📱 Invoice status tracking (Draft, Pending, Sent, Paid, etc.)
- 💾 Store PDFs on any Laravel filesystem disk
- 📧 Email invoice capabilities
- 🔄 Status history tracking
- 🏢 Configurable company and customer data

## Installation

Install the package via Composer:

```bash
composer require masyukai/invoice
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=invoice-config
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

The package configuration is located in `config/invoice.php`. You can customize:

- Invoice number format
- Default template
- PDF storage settings
- Company information
- Currency and tax defaults

## Usage

### Creating an Invoice

```php
use MasyukAI\Invoice\Facades\Invoice;
use MasyukAI\Invoice\DataObjects\InvoiceData;

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
use MasyukAI\Invoice\Enums\InvoiceStatus;

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

1. Create a new Blade view in `resources/views/vendor/invoice/templates/`:

```blade
<!-- resources/views/vendor/invoice/templates/modern.blade.php -->
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
use MasyukAI\Invoice\Models\InvoiceTemplate;

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
