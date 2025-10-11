<?php

declare(strict_types=1);

use AIArmada\Docs\DataObjects\DocumentData;
use AIArmada\Docs\Enums\DocumentStatus;
use AIArmada\Docs\Models\Document;
use AIArmada\Docs\Models\DocumentTemplate;
use AIArmada\Docs\Services\DocumentService;

test('it can generate document numbers', function (): void {
    $service = new DocumentService;
    $number = $service->generateDocumentNumber('invoice');

    expect($number)
        ->toBeString()
        ->toMatch('/^INV\d{2}-[A-Z0-9]{6}$/');
});

test('it can create a document', function (): void {
    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'document_type' => 'invoice',
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

    expect($document)
        ->toBeInstanceOf(Document::class)
        ->and($document->document_number)->toBeString()
        ->and($document->document_type)->toBe('invoice')
        ->and($document->status)->toBe(DocumentStatus::DRAFT)
        ->and($document->subtotal)->toBe('250.00')
        ->and($document->total)->toBe('250.00')
        ->and($document->items)->toBeArray()->toHaveCount(2);
});

test('it calculates totals correctly', function (): void {
    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'items' => [
            ['name' => 'Item 1', 'quantity' => 2, 'price' => 50.00],
            ['name' => 'Item 2', 'quantity' => 3, 'price' => 30.00],
        ],
        'tax_rate' => 0.06,
        'discount_amount' => 10.00,
    ]));

    expect($document->subtotal)->toBe('190.00')
        ->and($document->tax_amount)->toBe('11.40')
        ->and($document->discount_amount)->toBe('10.00')
        ->and($document->total)->toBe('191.40');
});

test('it can update document status', function (): void {
    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'items' => [['name' => 'Item', 'quantity' => 1, 'price' => 100]],
    ]));

    expect($document->status)->toBe(DocumentStatus::DRAFT);

    $service->updateDocumentStatus($document, DocumentStatus::PAID, 'Payment received');

    $document->refresh();
    expect($document->status)->toBe(DocumentStatus::PAID);

    $history = $document->statusHistories()->first();
    expect($history)
        ->not->toBeNull()
        ->and($history->status)->toBe(DocumentStatus::PAID)
        ->and($history->notes)->toBe('Payment received');
});

test('it can mark document as paid', function (): void {
    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'items' => [['name' => 'Item', 'quantity' => 1, 'price' => 100]],
    ]));

    expect($document->isPaid())->toBeFalse();

    $document->markAsPaid();
    $document->refresh();

    expect($document->isPaid())->toBeTrue()
        ->and($document->status)->toBe(DocumentStatus::PAID)
        ->and($document->paid_at)->not->toBeNull();
});

test('it can check if document is overdue', function (): void {
    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'items' => [['name' => 'Item', 'quantity' => 1, 'price' => 100]],
        'due_date' => now()->subDay(),
    ]));

    expect($document->isOverdue())->toBeTrue();

    $document->markAsPaid();
    expect($document->isOverdue())->toBeFalse();
});

test('it uses default template when none specified', function (): void {
    DocumentTemplate::create([
        'name' => 'Test Default',
        'slug' => 'test-default',
        'view_name' => 'test-default',
        'document_type' => 'invoice',
        'is_default' => true,
    ]);

    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'items' => [['name' => 'Item', 'quantity' => 1, 'price' => 100]],
    ]));

    expect($document->template)->not->toBeNull()
        ->and($document->template->slug)->toBe('test-default');
});

test('it can use custom template', function (): void {
    $template = DocumentTemplate::create([
        'name' => 'Custom Template',
        'slug' => 'custom',
        'view_name' => 'custom',
        'document_type' => 'invoice',
        'is_default' => false,
    ]);

    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'template_id' => $template->id,
        'items' => [['name' => 'Item', 'quantity' => 1, 'price' => 100]],
    ]));

    expect($document->template)->not->toBeNull()
        ->and($document->template->slug)->toBe('custom');
});

test('document status enum has correct labels', function (): void {
    expect(DocumentStatus::DRAFT->label())->toBe('Draft')
        ->and(DocumentStatus::PAID->label())->toBe('Paid')
        ->and(DocumentStatus::OVERDUE->label())->toBe('Overdue');
});

test('document status enum has correct colors', function (): void {
    expect(DocumentStatus::DRAFT->color())->toBe('gray')
        ->and(DocumentStatus::PAID->color())->toBe('success')
        ->and(DocumentStatus::OVERDUE->color())->toBe('danger');
});

test('it can check payable status', function (): void {
    expect(DocumentStatus::PENDING->isPayable())->toBeTrue()
        ->and(DocumentStatus::SENT->isPayable())->toBeTrue()
        ->and(DocumentStatus::PAID->isPayable())->toBeFalse()
        ->and(DocumentStatus::DRAFT->isPayable())->toBeFalse();
});

test('it supports backward compatibility with invoice keys', function (): void {
    $service = new DocumentService;

    $document = $service->createDocument(DocumentData::from([
        'invoice_number' => 'INV-123',
        'invoiceable_type' => 'App\\Models\\Order',
        'invoiceable_id' => '123',
        'items' => [['name' => 'Item', 'quantity' => 1, 'price' => 100]],
    ]));

    expect($document->document_number)->toBe('INV-123')
        ->and($document->documentable_type)->toBe('App\\Models\\Order')
        ->and($document->documentable_id)->toBe('123');
});
