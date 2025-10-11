# Package Rename: Invoice → Docs

## Overview

The package is named `masyukai/docs` to support multiple document types beyond just invoices.

## Changes Made

### 1. Package Structure
- **Directory renamed**: `packages/masyukai/invoice` → `packages/commerce/packages/docs`
- **Namespace changed**: `MasyukAI\Invoice` → `MasyukAI\Docs`
- **Package name**: `masyukai/invoice` → `masyukai/docs`

### 2. Core Files Updated

#### Composer Configuration
- Updated `composer.json` with new package name and description
- Changed autoload namespaces from `MasyukAI\Invoice` to `MasyukAI\Docs`
- Added "receipt" and "document" to keywords
- Updated service provider class name reference

#### Service Provider
- Renamed `InvoiceServiceProvider.php` → `DocsServiceProvider.php`
- Updated namespace to `MasyukAI\Docs`
- Changed package name from 'invoice' to 'docs'
- Added placeholder for future `ReceiptService`

#### Configuration
- Renamed `config/invoice.php` → `config/docs.php`
- Restructured config to support multiple document types
- Added `types` section with separate configs for 'invoice' and 'receipt'
- Maintained backward compatibility with legacy config keys
- Updated environment variable prefixes where appropriate

### 3. Document Type Support

#### Current Structure
```php
'types' => [
    'invoice' => [
        'default_template' => 'invoice-default',
        'number_format' => [...],
        'storage' => [...],
        'defaults' => [...],
    ],
    'receipt' => [
        'default_template' => 'receipt-default',
        'number_format' => [...],
        'storage' => [...],
        'defaults' => [...],
    ],
]
```

#### Templates
- Renamed `default.blade.php` → `invoice-default.blade.php`
- View namespace changed from `invoice::` to `docs::`
- Ready for additional templates: `receipt-default.blade.php`, etc.

### 4. All PHP Files Updated
- All source files: namespace changed to `MasyukAI\Docs`
- All models: `Invoice`, `InvoiceTemplate`, `InvoiceStatusHistory`
- All services: `InvoiceService` (receipt service placeholder added)
- All facades: `Invoice` (receipt facade available in config)
- All data objects: `InvoiceData`
- All enums: `InvoiceStatus`
- All tests: namespace and references updated

### 5. Documentation Updated
- `README.md`: Updated with new package name and multi-document focus
- `IMPLEMENTATION.md`: Updated all references
- `TAILWIND_USAGE.md`: Updated view paths and references
- `DOCS_PACKAGE_COMPLETE.md`: Renamed and updated from `INVOICE_PACKAGE_COMPLETE.md`
- `examples/invoice-usage.php`: Updated all namespaces

### 6. Main Application Integration
- Updated root `composer.json`:
  - Changed require from `masyukai/invoice` to `masyukai/docs`
  - Updated repository path
- Examples file updated with new namespaces

## Configuration Structure

The package uses a type-based configuration structure:

```php
'types' => [
    'invoice' => [
        'default_template' => 'invoice-default',
        'number_format' => [...],
        'storage' => [...],
        'defaults' => [...],
    ],
    'receipt' => [
        'default_template' => 'receipt-default',
        'number_format' => [...],
        'storage' => [...],
        'defaults' => [...],
    ],
]
```

## Usage
```php
use MasyukAI\Docs\Facades\Invoice;
use MasyukAI\Docs\DataObjects\InvoiceData;

## Usage

```php
use MasyukAI\Docs\Facades\Invoice;
use MasyukAI\Docs\DataObjects\InvoiceData;

$invoice = Invoice::createInvoice(InvoiceData::from([...]));
```

## Accessing Configuration

To access document type configurations:

```php
// Invoice configuration
config('docs.types.invoice.default_template')
config('docs.types.invoice.number_format.prefix')
config('docs.types.invoice.storage.disk')
config('docs.types.invoice.defaults.currency')

// Receipt configuration
config('docs.types.receipt.default_template')
config('docs.types.receipt.number_format.prefix')

// Shared configuration
config('docs.pdf.format')
config('docs.company.name')
```

## Future Expandability

### Adding Receipt Support
1. Create `ReceiptService` similar to `InvoiceService`
2. Create `Receipt` model, status enum, etc.
3. Create `receipt-default.blade.php` template
4. Uncomment receipt service registration in `DocsServiceProvider`
5. Use: `use MasyukAI\Docs\Facades\Receipt;`

### Adding Other Document Types
Follow the same pattern for quotations, purchase orders, delivery notes, etc.

## Benefits

1. **Scalable Architecture**: Easy to add new document types
2. **Consistent API**: Same patterns for all document types
3. **Type-specific Configuration**: Each document type has its own settings
4. **Shared Infrastructure**: PDF generation, storage, templates
5. **Future-proof**: Ready for receipts, quotations, and more
