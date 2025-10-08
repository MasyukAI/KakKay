<?php

declare(strict_types=1);

use MasyukAI\Invoice\DataObjects\InvoiceData;
use MasyukAI\Invoice\Enums\InvoiceStatus;
use MasyukAI\Invoice\Models\Invoice;
use MasyukAI\Invoice\Models\InvoiceTemplate;
use MasyukAI\Invoice\Services\InvoiceService;

test('it can generate invoice numbers', function (): void {
    $service = new InvoiceService;
    $number = $service->generateInvoiceNumber();

    expect($number)
        ->toBeString()
        ->toMatch('/^INV\d{2}-[A-Z0-9]{6}$/');
});

test('it can create an invoice', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [
            [
                'name' => 'Product A',
                'quantity' => 2,
                'price' => 100.00,
            ],
            [
                'name' => 'Product B',
                'quantity' => 1,
                'price' => 50.00,
            ],
        ],
        'customer_data' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
    ]));

    expect($invoice)
        ->toBeInstanceOf(Invoice::class)
        ->and($invoice->invoice_number)->toBeString()
        ->and($invoice->status)->toBe(InvoiceStatus::DRAFT)
        ->and($invoice->subtotal)->toBe('250.00')
        ->and($invoice->total)->toBe('250.00')
        ->and($invoice->items)->toBeArray()->toHaveCount(2);
});

test('it can calculate subtotal correctly', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [
            ['name' => 'Item 1', 'quantity' => 3, 'price' => 10.50],
            ['name' => 'Item 2', 'quantity' => 2, 'price' => 25.00],
        ],
    ]));

    expect($invoice->subtotal)->toBe('81.50');
});

test('it can apply tax', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [
            ['name' => 'Item 1', 'quantity' => 1, 'price' => 100.00],
        ],
        'tax_rate' => 0.06, // 6% tax
    ]));

    expect($invoice->subtotal)->toBe('100.00')
        ->and($invoice->tax_amount)->toBe('6.00')
        ->and($invoice->total)->toBe('106.00');
});

test('it can apply discount', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [
            ['name' => 'Item 1', 'quantity' => 1, 'price' => 100.00],
        ],
        'discount_amount' => 10.00,
    ]));

    expect($invoice->subtotal)->toBe('100.00')
        ->and($invoice->discount_amount)->toBe('10.00')
        ->and($invoice->total)->toBe('90.00');
});

test('it can mark invoice as paid', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [['name' => 'Test', 'quantity' => 1, 'price' => 100.00]],
        'status' => InvoiceStatus::PENDING,
    ]));

    expect($invoice->status)->toBe(InvoiceStatus::PENDING)
        ->and($invoice->paid_at)->toBeNull();

    $invoice->markAsPaid();
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::PAID)
        ->and($invoice->paid_at)->not->toBeNull();
});

test('it can mark invoice as sent', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [['name' => 'Test', 'quantity' => 1, 'price' => 100.00]],
        'status' => InvoiceStatus::DRAFT,
    ]));

    expect($invoice->status)->toBe(InvoiceStatus::DRAFT);

    $invoice->markAsSent();
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::SENT);
});

test('it can cancel invoice', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [['name' => 'Test', 'quantity' => 1, 'price' => 100.00]],
        'status' => InvoiceStatus::PENDING,
    ]));

    $invoice->cancel();
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::CANCELLED);
});

test('it cannot cancel paid invoice', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [['name' => 'Test', 'quantity' => 1, 'price' => 100.00]],
        'status' => InvoiceStatus::PAID,
    ]));

    $invoice->cancel();
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::PAID);
});

test('it can detect overdue invoices', function (): void {
    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [['name' => 'Test', 'quantity' => 1, 'price' => 100.00]],
        'status' => InvoiceStatus::SENT,
        'due_date' => now()->subDays(5),
    ]));

    expect($invoice->isOverdue())->toBeTrue();

    $invoice->updateStatus();
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::OVERDUE);
});

test('it can use custom invoice template', function (): void {
    $template = InvoiceTemplate::create([
        'name' => 'Custom Template',
        'slug' => 'custom',
        'view_name' => 'custom',
        'is_default' => false,
    ]);

    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'template_slug' => 'custom',
        'items' => [['name' => 'Test', 'quantity' => 1, 'price' => 100.00]],
    ]));

    expect($invoice->invoice_template_id)->toBe($template->id);
});

test('it uses default template when none specified', function (): void {
    $template = InvoiceTemplate::create([
        'name' => 'Default Template',
        'slug' => 'default',
        'view_name' => 'default',
        'is_default' => true,
    ]);

    $service = new InvoiceService;

    $invoice = $service->createInvoice(InvoiceData::from([
        'items' => [['name' => 'Test', 'quantity' => 1, 'price' => 100.00]],
    ]));

    expect($invoice->invoice_template_id)->toBe($template->id);
});

test('invoice status enum has correct labels', function (): void {
    expect(InvoiceStatus::DRAFT->label())->toBe('Draft')
        ->and(InvoiceStatus::PENDING->label())->toBe('Pending')
        ->and(InvoiceStatus::SENT->label())->toBe('Sent')
        ->and(InvoiceStatus::PAID->label())->toBe('Paid')
        ->and(InvoiceStatus::OVERDUE->label())->toBe('Overdue')
        ->and(InvoiceStatus::CANCELLED->label())->toBe('Cancelled');
});

test('invoice status enum has correct colors', function (): void {
    expect(InvoiceStatus::PAID->color())->toBe('success')
        ->and(InvoiceStatus::PENDING->color())->toBe('warning')
        ->and(InvoiceStatus::OVERDUE->color())->toBe('danger');
});
