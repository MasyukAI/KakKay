# Invoice Generation for Orders

## Summary

Implemented on-the-fly invoice generation for orders using the `masyukai/docs` (now `aiarmada/docs`) package. The system allows admins to generate and download invoices directly from the Filament admin panel.

## Changes Made

### 1. OrderResource Table Actions

**File:** `app/Filament/Resources/Orders/Tables/OrdersTable.php`

Added a "Generate Invoice" action to the table that:
- Checks for existing invoice documents
- Creates new invoices if none exist
- Converts order items to invoice items
- Maps customer and address data
- Generates PDF on-the-fly without saving to disk
- Streams PDF download to browser

### 2. ViewOrder Page Actions

**File:** `app/Filament/Resources/Orders/Pages/ViewOrder.php`

Added a "Generate Invoice" button to the order view page header with the same functionality as the table action.

### 3. Order Model Relationships

**File:** `app/Models/Order.php`

Added polymorphic relationships:
```php
// Get all documents (invoices, receipts, etc.)
public function documents(): MorphMany

// Get only invoices  
public function invoices(): MorphMany
```

### 4. Custom Invoice Template

**File:** `resources/views/vendor/docs/templates/invoice-kakkay.blade.php`

Created a branded invoice template with:
- Kakkay gradient header (pink/rose/purple)
- Malay language labels
- Clean, modern design using Tailwind CSS
- Company and customer information sections
- Itemized list of products
- Totals with discounts and tax support

### 5. Configuration

**File:** `config/docs.php`

Created app-specific configuration:
- Set default template to `invoice-kakkay`
- Configured company information (Kak Kay)
- Set invoice numbering format (INV-{year}-{suffix})
- Configured storage settings

### 6. Package Registration

**Files Modified:**
- `packages/commerce/composer.json` - Added DocsServiceProvider and Document facade to auto-discovery
- `composer.json` - Added `aiarmada/docs` to requirements

### 7. Tests

**File:** `tests/Feature/Orders/OrderInvoiceTest.php`

Created comprehensive tests for:
- Invoice document creation
- PDF generation
- Order-Document relationships
- Retrieving existing invoices

## Features

### On-the-Fly Generation
Invoices are generated in memory and streamed directly to the browser without saving to disk by default. This:
- Reduces storage requirements
- Allows dynamic data updates
- Simplifies cleanup

### Automatic or Manual Creation
- Invoices can be generated on-demand from the admin panel
- Each order can have multiple documents (invoices, receipts, etc.)
- Documents are created once and reused on subsequent requests

### Data Mapping
The system automatically converts:
- Order items → Invoice line items
- Order total (cents) → MYR currency format
- User & Address → Customer data
- Order metadata → Invoice notes

### Branded Template
The custom `invoice-kakkay` template provides:
- Gradient header matching Kakkay brand colors
- Malay language for local market
- Professional PDF output
- Responsive layout

## Usage

### From Filament Admin

1. **List View:** Click the "Generate Invoice" action on any order row
2. **Detail View:** Click the "Generate Invoice" button in the header
3. **Result:** PDF downloads automatically to browser

### Programmatically

```php
use AIArmada\Docs\DataObjects\DocumentData;
use AIArmada\Docs\Enums\DocumentStatus;
use AIArmada\Docs\Facades\Document;

// Prepare invoice data
$documentData = DocumentData::from([
    'document_type' => 'invoice',
    'documentable_type' => Order::class,
    'documentable_id' => $order->id,
    'status' => DocumentStatus::PAID,
    'issue_date' => $order->created_at,
    'items' => $items, // Array of line items
    'customer_data' => $customerData,
    'currency' => 'MYR',
]);

// Create document
$document = Document::createDocument($documentData);

// Generate PDF (in memory, not saved)
$pdfContent = Document::generatePdf($document, false);

// Or save to storage
$url = Document::generatePdf($document, true);
```

### Accessing Invoices

```php
// Get all invoices for an order
$invoices = $order->invoices;

// Get all documents (invoices, receipts, etc.)
$documents = $order->documents;

// Check if order has invoice
$hasInvoice = $order->invoices()->exists();
```

## Technical Details

### Document Structure

Each document contains:
- **document_number:** Auto-generated (e.g., INV25-ABC123)
- **document_type:** invoice, receipt, etc.
- **documentable:** Polymorphic relation to Order
- **status:** draft, sent, paid, cancelled, etc.
- **items:** JSON array of line items
- **customer_data:** JSON object with customer info
- **company_data:** JSON object with company info
- **totals:** subtotal, tax, discount, total

### Address Field Mapping

The system uses the correct database column names:
- `street1` → Main address line
- `street2` → Secondary address line  
- `postcode` → Postal code

### Currency Handling

All amounts are:
- Stored in cents (integer) in orders table
- Converted to MYR (float) for invoices
- Formatted with 2 decimal places in PDF

## Future Enhancements

Potential improvements:
1. Email invoice to customer
2. Bulk invoice generation
3. Invoice preview before download
4. Custom invoice templates per product/category
5. Multi-currency support
6. Invoice versioning/revisions
7. Automated invoice generation on order completion

## Migration Notes

The docs package migration was already run:
```bash
php artisan migrate --path=packages/commerce/packages/docs/database/migrations
```

Creates tables:
- `documents` - Main document storage
- `document_templates` - Template configurations  
- `document_status_histories` - Status change tracking

## Dependencies

- `spatie/laravel-pdf` - PDF generation
- `tailwindcss` - Template styling (CDN)
- Laravel 12
- Filament 4
