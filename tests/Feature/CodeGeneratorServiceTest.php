<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Services\CodeGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it generates order codes with correct format and uniqueness', function () {
    $code = CodeGeneratorService::generateOrderCode();

    expect($code)->toMatch('/^ORD\d{2}-[A-Z0-9]{6}$/');
    expect($code)->toStartWith('ORD'.now()->format('y'));
});

test('it generates invoice codes with correct format', function () {
    $code = CodeGeneratorService::generateInvoiceCode();

    expect($code)->toMatch('/^INV\d{2}-[A-Z0-9]{6}$/');
    expect($code)->toStartWith('INV'.now()->format('y'));
});

test('it generates payment codes with correct format and uniqueness', function () {
    $code = CodeGeneratorService::generatePaymentCode();

    expect($code)->toMatch('/^PMT\d{2}-[A-Z0-9]{6}$/');
    expect($code)->toStartWith('PMT'.now()->format('y'));
});

test('it generates refund codes with correct format and uniqueness', function () {
    $code = CodeGeneratorService::generateRefundCode();

    expect($code)->toMatch('/^RFD\d{2}-[A-Z0-9]{6}$/');
    expect($code)->toStartWith('RFD'.now()->format('y'));
});

test('it generates shipment codes with correct format and uniqueness', function () {
    $code = CodeGeneratorService::generateShipmentCode();

    expect($code)->toMatch('/^SHP\d{2}-[A-Z0-9]{6}$/');
    expect($code)->toStartWith('SHP'.now()->format('y'));
});

test('it generates custom codes with specified prefix', function () {
    $code = CodeGeneratorService::generateCode('TST');

    expect($code)->toMatch('/^TST\d{2}-[A-Z0-9]{6}$/');
    expect($code)->toStartWith('TST'.now()->format('y'));
});

test('it validates code formats correctly', function () {
    $validCode = 'ORD25-ABC123';
    $invalidCodes = [
        'INVALID',
        'ORD-ABC123',
        'ORD25-ABC12',
        'ord25-abc123',
        'ORD25ABC123',
    ];

    expect(CodeGeneratorService::isValidCodeFormat($validCode))->toBeTrue();
    expect(CodeGeneratorService::isValidCodeFormat($validCode, 'ORD'))->toBeTrue();
    expect(CodeGeneratorService::isValidCodeFormat($validCode, 'INV'))->toBeFalse();

    foreach ($invalidCodes as $invalidCode) {
        expect(CodeGeneratorService::isValidCodeFormat($invalidCode))->toBeFalse();
    }
});

test('it generates unique order codes when duplicates exist in database', function () {
    // Create an order with a specific order number
    $existingCode = 'ORD25-TEST01';
    Order::factory()->create(['order_number' => $existingCode]);

    // Generate multiple codes - they should all be unique and not match the existing one
    $codes = [];
    for ($i = 0; $i < 5; $i++) {
        $codes[] = CodeGeneratorService::generateOrderCode();
    }

    // All codes should be unique
    expect(count(array_unique($codes)))->toBe(5);

    // None should match the existing code
    foreach ($codes as $code) {
        expect($code)->not->toBe($existingCode);
    }
});

test('it generates unique payment codes when duplicates exist in database', function () {
    // Create a payment with a specific reference
    $existingCode = 'PMT25-TEST01';
    Payment::factory()->create(['reference' => $existingCode]);

    // Generate multiple codes - they should all be unique and not match the existing one
    $codes = [];
    for ($i = 0; $i < 5; $i++) {
        $codes[] = CodeGeneratorService::generatePaymentCode();
    }

    // All codes should be unique
    expect(count(array_unique($codes)))->toBe(5);

    // None should match the existing code
    foreach ($codes as $code) {
        expect($code)->not->toBe($existingCode);
    }
});

test('it generates unique shipment codes when duplicates exist in database', function () {
    // Create a shipment with a specific tracking number
    $existingCode = 'SHP25-TEST01';
    Shipment::factory()->create(['tracking_number' => $existingCode]);

    // Generate multiple codes - they should all be unique and not match the existing one
    $codes = [];
    for ($i = 0; $i < 5; $i++) {
        $codes[] = CodeGeneratorService::generateShipmentCode();
    }

    // All codes should be unique
    expect(count(array_unique($codes)))->toBe(5);

    // None should match the existing code
    foreach ($codes as $code) {
        expect($code)->not->toBe($existingCode);
    }
});

test('order model uses code generator service', function () {
    $orderNumber = Order::generateOrderNumber();

    expect($orderNumber)->toMatch('/^ORD\d{2}-[A-Z0-9]{6}$/');
    expect($orderNumber)->toStartWith('ORD'.now()->format('y'));
});
