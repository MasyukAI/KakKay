<?php

declare(strict_types=1);

/**
 * Example usage of the Invoice package
 *
 * This file demonstrates how to integrate the invoice package with your application.
 * It shows various use cases for generating, managing, and delivering invoices.
 */

use MasyukAI\Docs\DataObjects\InvoiceData;
use MasyukAI\Docs\Enums\InvoiceStatus;
use MasyukAI\Docs\Facades\Invoice;
use MasyukAI\Docs\Models\InvoiceTemplate;

// ============================================================================
// Example 1: Create a simple invoice for an order
// ============================================================================

/**
 * Create an invoice for a completed order
 */
function createInvoiceForOrder($order): MasyukAI\Invoice\Models\Invoice
{
    $invoice = Invoice::createInvoice(InvoiceData::from([
        // Link the invoice to the order
        'invoiceable_type' => 'App\\Models\\Order',
        'invoiceable_id' => $order->id,

        // Invoice status
        'status' => InvoiceStatus::PENDING,

        // Line items from order
        'items' => $order->items->map(fn ($item) => [
            'name' => $item->product->name,
            'description' => $item->product->description,
            'quantity' => $item->quantity,
            'price' => (float) $item->unit_price,
        ])->toArray(),

        // Customer information
        'customer_data' => [
            'name' => $order->user->name,
            'email' => $order->user->email,
            'address' => $order->shipping_address->address_line_1 ?? '',
            'city' => $order->shipping_address->city ?? '',
            'state' => $order->shipping_address->state ?? '',
            'postal_code' => $order->shipping_address->postal_code ?? '',
            'country' => $order->shipping_address->country ?? '',
            'phone' => $order->user->phone ?? '',
        ],

        // Company information (optional - uses config defaults if not provided)
        'company_data' => config('invoice.company'),

        // Tax and discounts
        'tax_amount' => (float) $order->tax_amount,
        'discount_amount' => (float) $order->discount_amount,

        // Currency
        'currency' => $order->currency ?? 'MYR',

        // Additional info
        'notes' => 'Thank you for your order!',
        'terms' => 'Payment due within 30 days. All sales are final.',

        // Auto-generate PDF
        'generate_pdf' => true,
    ]));

    return $invoice;
}

// ============================================================================
// Example 2: Generate invoice after payment is successful
// ============================================================================

/**
 * Generate and email invoice after successful payment
 */
function generateInvoiceOnPaymentSuccess($payment): void
{
    $order = $payment->order;

    // Create the invoice
    $invoice = Invoice::createInvoice(InvoiceData::from([
        'invoiceable_type' => 'App\\Models\\Payment',
        'invoiceable_id' => $payment->id,
        'status' => InvoiceStatus::PAID,
        'paid_at' => $payment->paid_at,
        'items' => $order->items->map(fn ($item) => [
            'name' => $item->product->name,
            'quantity' => $item->quantity,
            'price' => (float) $item->unit_price,
        ])->toArray(),
        'customer_data' => [
            'name' => $order->user->name,
            'email' => $order->user->email,
        ],
        'generate_pdf' => true,
    ]));

    // Email the invoice to the customer
    Invoice::emailInvoice($invoice, $order->user->email);
}

// ============================================================================
// Example 3: Provide invoice download link on success page
// ============================================================================

/**
 * Get download URL for invoice on order success page
 */
function getInvoiceDownloadUrl($order): ?string
{
    $invoice = MasyukAI\Invoice\Models\Invoice::where('invoiceable_type', 'App\\Models\\Order')
        ->where('invoiceable_id', $order->id)
        ->first();

    if ($invoice) {
        return Invoice::downloadPdf($invoice);
    }

    return null;
}

// ============================================================================
// Example 4: Display invoices in user portal
// ============================================================================

/**
 * Get all invoices for a user to display in their dashboard
 */
function getUserInvoices($user)
{
    return MasyukAI\Invoice\Models\Invoice::whereHasMorph(
        'invoiceable',
        ['App\\Models\\Order', 'App\\Models\\Payment'],
        function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }
    )->orderBy('created_at', 'desc')->get();
}

// ============================================================================
// Example 5: Create custom invoice template
// ============================================================================

/**
 * Create a custom branded invoice template
 */
function createCustomTemplate(): InvoiceTemplate
{
    return InvoiceTemplate::create([
        'name' => 'Premium Template',
        'slug' => 'premium',
        'description' => 'Premium branded invoice template with company logo',
        'view_name' => 'premium', // This should match a view in resources/views/vendor/invoice/templates/
        'is_default' => false,
        'settings' => [
            'show_logo' => true,
            'primary_color' => '#3490dc',
            'font_family' => 'Inter',
        ],
    ]);
}

// ============================================================================
// Example 6: Update invoice status manually
// ============================================================================

/**
 * Mark an invoice as paid when payment is confirmed
 */
function markInvoiceAsPaid($invoice, $notes = null): void
{
    Invoice::updateInvoiceStatus(
        $invoice,
        InvoiceStatus::PAID,
        $notes ?? 'Payment confirmed via bank transfer'
    );
}

// ============================================================================
// Example 7: Check for overdue invoices and send reminders
// ============================================================================

/**
 * Find overdue invoices and update their status
 */
function updateOverdueInvoices(): void
{
    $invoices = MasyukAI\Invoice\Models\Invoice::whereIn('status', [
        InvoiceStatus::PENDING,
        InvoiceStatus::SENT,
    ])->whereDate('due_date', '<', now())->get();

    foreach ($invoices as $invoice) {
        $invoice->updateStatus();

        // Send reminder email (implement your email logic)
        // Mail::to($invoice->customer_data['email'])->send(new InvoiceOverdueReminder($invoice));
    }
}

// ============================================================================
// Example 8: Create invoice with custom numbering
// ============================================================================

/**
 * Create invoice with custom invoice number
 */
function createInvoiceWithCustomNumber($order, $customNumber): MasyukAI\Invoice\Models\Invoice
{
    return Invoice::createInvoice(InvoiceData::from([
        'invoice_number' => $customNumber,
        'invoiceable_type' => 'App\\Models\\Order',
        'invoiceable_id' => $order->id,
        'items' => [
            ['name' => 'Custom Service', 'quantity' => 1, 'price' => 1000.00],
        ],
        'customer_data' => [
            'name' => $order->user->name,
            'email' => $order->user->email,
        ],
    ]));
}

// ============================================================================
// Example 9: Integration with order creation
// ============================================================================

/**
 * This is how you might integrate invoice generation into your order workflow
 */
class OrderService
{
    public function completeOrder($order): void
    {
        // Mark order as complete
        $order->update(['status' => 'completed']);

        // Generate invoice
        $invoice = Invoice::createInvoice(InvoiceData::from([
            'invoiceable_type' => 'App\\Models\\Order',
            'invoiceable_id' => $order->id,
            'status' => InvoiceStatus::PAID,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => (float) $item->unit_price,
            ])->toArray(),
            'customer_data' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'tax_amount' => (float) $order->tax_amount,
            'generate_pdf' => true,
        ]));

        // Store invoice reference in order
        $order->update(['invoice_url' => Invoice::downloadPdf($invoice)]);

        // Email invoice to customer
        Invoice::emailInvoice($invoice, $order->user->email);
    }
}

// ============================================================================
// Example 10: Generate invoice on-the-fly for viewing
// ============================================================================

/**
 * Generate invoice PDF on-the-fly without saving
 */
function previewInvoicePdf($invoice): string
{
    return Invoice::generatePdf($invoice, save: false);
}
