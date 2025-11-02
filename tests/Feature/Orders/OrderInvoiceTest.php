<?php

declare(strict_types=1);

use AIArmada\Docs\DataObjects\DocData;
use AIArmada\Docs\Enums\DocStatus;
use AIArmada\Docs\Facades\Doc;
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

    $docData = DocData::from([
        'doc_type' => 'invoice',
        'docable_type' => Order::class,
        'docable_id' => $this->order->id,
        'status' => DocStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'notes' => "Order Number: {$this->order->order_number}",
        'generate_pdf' => false,
    ]);

    $doc = Doc::createDoc($docData);

    expect($doc)->toBeInstanceOf(AIArmada\Docs\Models\Doc::class)
        ->and($doc->doc_type)->toBe('invoice')
        ->and($doc->docable_type)->toBe(Order::class)
        ->and($doc->docable_id)->toBe($this->order->id)
        ->and($doc->status)->toBe(DocStatus::PAID)
        ->and($doc->total)->toBe('50.00')
        ->and($doc->customer_data['name'])->toBe('John Doe')
        ->and($doc->customer_data['email'])->toBe('john@example.com');
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

    $docData = DocData::from([
        'doc_type' => 'invoice',
        'docable_type' => Order::class,
        'docable_id' => $this->order->id,
        'status' => DocStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'notes' => "Order Number: {$this->order->order_number}",
        'generate_pdf' => false,
    ]);

    $document = Doc::createDoc($docData);

    // Generate PDF content
    $pdfContent = Doc::generatePdf($document, false);

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

    $docData = DocData::from([
        'doc_type' => 'invoice',
        'docable_type' => Order::class,
        'docable_id' => $this->order->id,
        'status' => DocStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'generate_pdf' => false,
    ]);

    $document = Doc::createDoc($docData);

    $this->order->refresh();

    expect($this->order->docs)->toHaveCount(1)
        ->and($this->order->docs->first()->doc_type)->toBe('invoice')
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

    $docData = DocData::from([
        'doc_type' => 'invoice',
        'docable_type' => Order::class,
        'docable_id' => $this->order->id,
        'status' => DocStatus::PAID,
        'issue_date' => $this->order->created_at,
        'due_date' => $this->order->created_at,
        'currency' => 'MYR',
        'items' => $items,
        'customer_data' => $customerData,
        'generate_pdf' => false,
    ]);

    $document = Doc::createDoc($docData);

    // Retrieve existing document
    $existingDoc = AIArmada\Docs\Models\Doc::query()
        ->where('docable_type', Order::class)
        ->where('docable_id', $this->order->id)
        ->where('doc_type', 'invoice')
        ->first();

    expect($existingDoc)->not->toBeNull()
        ->and($existingDoc->id)->toBe($document->id)
        ->and($existingDoc->doc_number)->toBe($document->doc_number);
});
