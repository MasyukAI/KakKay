<?php

declare(strict_types=1);

use AIArmada\Docs\DataObjects\DocumentData;
use AIArmada\Docs\Enums\DocumentStatus;
use AIArmada\Docs\Facades\Document;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->address = Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $this->user->id,
        'name' => 'John Doe',
        'phone' => '+60123456789',
        'street1' => '123 Jalan Test',
        'street2' => 'Taman Test',
        'city' => 'Kuala Lumpur',
        'state' => 'Wilayah Persekutuan',
        'postcode' => '50000',
    ]);

    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'address_id' => $this->address->id,
        'status' => 'completed',
        'total' => 5000, // RM 50.00
        'cart_items' => [
            [
                'id' => '1',
                'name' => 'Test Product',
                'slug' => 'test-product',
                'price' => 5000,
                'quantity' => 1,
            ],
        ],
    ]);
});

test('can generate invoice document for order', function () {
    $items = [
        [
            'description' => 'Test Product',
            'quantity' => 1,
            'price' => 50.00,
            'amount' => 50.00,
        ],
    ];

    $customerData = [
        'name' => $this->user->name,
        'email' => $this->user->email,
        'address' => $this->address->street1,
        'city' => $this->address->city,
        'state' => $this->address->state,
        'postal_code' => $this->address->postcode,
        'phone' => $this->address->phone,
    ];

    $documentData = DocumentData::from([
        'document_type' => 'invoice',
        'documentable_type' => Order::class,
        'documentable_id' => $this->order->id,
        'status' => DocumentStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'notes' => "Order Number: {$this->order->order_number}",
        'generate_pdf' => false,
    ]);

    $document = Document::createDocument($documentData);

    expect($document)->toBeInstanceOf(AIArmada\Docs\Models\Document::class)
        ->and($document->document_type)->toBe('invoice')
        ->and($document->documentable_type)->toBe(Order::class)
        ->and($document->documentable_id)->toBe($this->order->id)
        ->and($document->status)->toBe(DocumentStatus::PAID)
        ->and($document->total)->toBe('50.00')
        ->and($document->customer_data['name'])->toBe('John Doe')
        ->and($document->customer_data['email'])->toBe('john@example.com');
});

test('can generate PDF for invoice document', function () {
    $items = [
        [
            'description' => 'Test Product',
            'quantity' => 1,
            'price' => 50.00,
            'amount' => 50.00,
        ],
    ];

    $customerData = [
        'name' => $this->user->name,
        'email' => $this->user->email,
        'address' => $this->address->street1,
        'city' => $this->address->city,
        'state' => $this->address->state,
        'postal_code' => $this->address->postcode,
        'phone' => $this->address->phone,
    ];

    $documentData = DocumentData::from([
        'document_type' => 'invoice',
        'documentable_type' => Order::class,
        'documentable_id' => $this->order->id,
        'status' => DocumentStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'notes' => "Order Number: {$this->order->order_number}",
        'generate_pdf' => false,
    ]);

    $document = Document::createDocument($documentData);

    // Generate PDF content
    $pdfContent = Document::generatePdf($document, false);

    expect($pdfContent)->toBeString()
        ->and(mb_strlen($pdfContent))->toBeGreaterThan(0);
});

test('order has documents relationship', function () {
    $items = [
        [
            'description' => 'Test Product',
            'quantity' => 1,
            'price' => 50.00,
            'amount' => 50.00,
        ],
    ];

    $customerData = [
        'name' => $this->user->name,
        'email' => $this->user->email,
    ];

    $documentData = DocumentData::from([
        'document_type' => 'invoice',
        'documentable_type' => Order::class,
        'documentable_id' => $this->order->id,
        'status' => DocumentStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'generate_pdf' => false,
    ]);

    $document = Document::createDocument($documentData);

    $this->order->refresh();

    expect($this->order->documents)->toHaveCount(1)
        ->and($this->order->documents->first()->document_type)->toBe('invoice')
        ->and($this->order->invoices)->toHaveCount(1);
});

test('can retrieve existing invoice for order', function () {
    $items = [
        [
            'description' => 'Test Product',
            'quantity' => 1,
            'price' => 50.00,
            'amount' => 50.00,
        ],
    ];

    $customerData = [
        'name' => $this->user->name,
        'email' => $this->user->email,
    ];

    $documentData = DocumentData::from([
        'document_type' => 'invoice',
        'documentable_type' => Order::class,
        'documentable_id' => $this->order->id,
        'status' => DocumentStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'generate_pdf' => false,
    ]);

    $document = Document::createDocument($documentData);

    // Retrieve existing document
    $existingDocument = AIArmada\Docs\Models\Document::query()
        ->where('documentable_type', Order::class)
        ->where('documentable_id', $this->order->id)
        ->where('document_type', 'invoice')
        ->first();

    expect($existingDocument)->not->toBeNull()
        ->and($existingDocument->id)->toBe($document->id)
        ->and($existingDocument->document_number)->toBe($document->document_number);
});
