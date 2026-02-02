<?php

declare(strict_types=1);

use AIArmada\Chip\Data\PurchaseData as Purchase;
use App\Services\Chip\ChipDataRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Ensure chip_purchases table exists
    if (! Schema::hasTable('chip_purchases')) {
        $this->markTestSkipped('chip_purchases table does not exist');
    }
});

test('chip purchase with empty string UUIDs are stored as null in database', function () {
    $recorder = app(ChipDataRecorder::class);

    // Simulate CHIP API payload with empty strings for optional UUID fields
    // This is the real scenario that was causing the PostgreSQL error
    $payload = [
        'id' => 'test-purchase-'.uniqid(),
        'type' => 'purchase',
        'status' => 'paid',
        'created_on' => time(),
        'updated_on' => time(),
        'client' => [
            'email' => 'test@example.com',
            'full_name' => 'Test User',
        ],
        'purchase' => [
            'currency' => 'MYR',
            'total' => 10000,
            'products' => [],
        ],
        'brand_id' => '550e8400-e29b-41d4-a716-446655440000',
        'company_id' => '', // Empty string from CHIP API
        'user_id' => '', // Empty string from CHIP API
        'billing_template_id' => '', // Empty string from CHIP API
        'client_id' => '', // Empty string from CHIP API
        'issuer_details' => [
            'legal_name' => '',
            'brand_name' => null,
        ],
        'transaction_data' => [
            'payment_method' => '',
            'extra' => [],
        ],
        'status_history' => [],
        'viewed_on' => null,
        'send_receipt' => false,
        'is_test' => true,
        'is_recurring_token' => false,
        'recurring_token' => '', // Empty string from CHIP API
        'skip_capture' => false,
        'force_recurring' => false,
        'reference_generated' => '1',
        'reference' => 'test-ref-'.uniqid(),
        'notes' => null,
        'issued' => null,
        'due' => null,
        'refund_availability' => 'all',
        'refundable_amount' => 0,
        'currency_conversion' => null,
        'payment_method_whitelist' => [],
        'success_redirect' => null,
        'failure_redirect' => null,
        'cancel_redirect' => null,
        'success_callback' => null,
        'creator_agent' => null,
        'platform' => 'api',
        'product' => 'purchases',
        'created_from_ip' => null,
        'invoice_url' => null,
        'checkout_url' => null,
        'direct_post_url' => null,
        'marked_as_paid' => false,
        'order_id' => null,
        'payment' => null,
    ];

    // This should NOT throw a PostgreSQL UUID error
    $purchase = $recorder->upsertPurchase($payload);

    expect($purchase)->toBeInstanceOf(Purchase::class);
    expect($purchase->id)->toBe($payload['id']);

    // Verify the record was stored in the database
    $stored = DB::table('chip_purchases')
        ->where('id', $payload['id'])
        ->first();

    expect($stored)->not->toBeNull();

    // Critical: Verify empty strings were converted to NULL
    expect($stored->company_id)->toBeNull();
    expect($stored->user_id)->toBeNull();
    expect($stored->billing_template_id)->toBeNull();
    expect($stored->client_id)->toBeNull();
    expect($stored->recurring_token)->toBeNull();

    // Verify non-empty UUID was stored correctly
    expect($stored->brand_id)->toBe($payload['brand_id']);
    expect($stored->status)->toBe('paid');
    expect($stored->is_test)->toBe(1); // Stored as integer in database
});

test('chip purchase with valid UUIDs are stored correctly', function () {
    $recorder = app(ChipDataRecorder::class);

    $validUserId = '660e8400-e29b-41d4-a716-446655440001';
    $validClientId = '770e8400-e29b-41d4-a716-446655440002';

    $payload = [
        'id' => 'test-purchase-'.uniqid(),
        'type' => 'purchase',
        'status' => 'created',
        'created_on' => time(),
        'updated_on' => time(),
        'client' => [
            'email' => 'test@example.com',
            'full_name' => 'Test User',
        ],
        'purchase' => [
            'currency' => 'MYR',
            'total' => 5000,
            'products' => [],
        ],
        'brand_id' => '550e8400-e29b-41d4-a716-446655440000',
        'company_id' => '', // Still empty
        'user_id' => $validUserId, // Valid UUID
        'billing_template_id' => '', // Still empty
        'client_id' => $validClientId, // Valid UUID
        'issuer_details' => [
            'legal_name' => '',
            'brand_name' => null,
        ],
        'transaction_data' => [
            'payment_method' => '',
            'extra' => [],
        ],
        'status_history' => [],
        'viewed_on' => null,
        'send_receipt' => false,
        'is_test' => true,
        'is_recurring_token' => false,
        'recurring_token' => null,
        'skip_capture' => false,
        'force_recurring' => false,
        'reference_generated' => '1',
        'reference' => 'test-ref-'.uniqid(),
        'notes' => null,
        'issued' => null,
        'due' => null,
        'refund_availability' => 'all',
        'refundable_amount' => 0,
        'currency_conversion' => null,
        'payment_method_whitelist' => [],
        'success_redirect' => null,
        'failure_redirect' => null,
        'cancel_redirect' => null,
        'success_callback' => null,
        'creator_agent' => null,
        'platform' => 'api',
        'product' => 'purchases',
        'created_from_ip' => null,
        'invoice_url' => null,
        'checkout_url' => null,
        'direct_post_url' => null,
        'marked_as_paid' => false,
        'order_id' => null,
        'payment' => null,
    ];

    $purchase = $recorder->upsertPurchase($payload);

    $stored = DB::table('chip_purchases')
        ->where('id', $payload['id'])
        ->first();

    expect($stored)->not->toBeNull();

    // Empty strings converted to NULL
    expect($stored->company_id)->toBeNull();
    expect($stored->billing_template_id)->toBeNull();

    // Valid UUIDs stored correctly
    expect($stored->user_id)->toBe($validUserId);
    expect($stored->client_id)->toBe($validClientId);
});

test('chip purchase upsert updates existing record', function () {
    $recorder = app(ChipDataRecorder::class);

    $purchaseId = 'test-purchase-'.uniqid();

    // First insert
    $payload = [
        'id' => $purchaseId,
        'type' => 'purchase',
        'status' => 'created',
        'created_on' => time(),
        'updated_on' => time(),
        'client' => [
            'email' => 'test@example.com',
            'full_name' => 'Test User',
        ],
        'purchase' => [
            'currency' => 'MYR',
            'total' => 10000,
            'products' => [],
        ],
        'brand_id' => '550e8400-e29b-41d4-a716-446655440000',
        'company_id' => '',
        'user_id' => '',
        'billing_template_id' => '',
        'client_id' => '',
        'issuer_details' => [
            'legal_name' => '',
            'brand_name' => null,
        ],
        'transaction_data' => [
            'payment_method' => '',
            'extra' => [],
        ],
        'status_history' => [],
        'viewed_on' => null,
        'send_receipt' => false,
        'is_test' => true,
        'is_recurring_token' => false,
        'recurring_token' => '',
        'skip_capture' => false,
        'force_recurring' => false,
        'reference_generated' => '1',
        'reference' => 'test-ref-'.uniqid(),
        'notes' => null,
        'issued' => null,
        'due' => null,
        'refund_availability' => 'all',
        'refundable_amount' => 0,
        'currency_conversion' => null,
        'payment_method_whitelist' => [],
        'success_redirect' => null,
        'failure_redirect' => null,
        'cancel_redirect' => null,
        'success_callback' => null,
        'creator_agent' => null,
        'platform' => 'api',
        'product' => 'purchases',
        'created_from_ip' => null,
        'invoice_url' => null,
        'checkout_url' => null,
        'direct_post_url' => null,
        'marked_as_paid' => false,
        'order_id' => null,
        'payment' => null,
    ];

    $recorder->upsertPurchase($payload);

    $firstStored = DB::table('chip_purchases')
        ->where('id', $purchaseId)
        ->first();

    expect($firstStored->status)->toBe('created');

    // Update to paid status
    $payload['status'] = 'paid';
    $payload['updated_on'] = time() + 100;

    $recorder->upsertPurchase($payload);

    $updated = DB::table('chip_purchases')
        ->where('id', $purchaseId)
        ->first();

    // Should be updated, not duplicated
    expect($updated->status)->toBe('paid');

    // Should only be one record
    $count = DB::table('chip_purchases')
        ->where('id', $purchaseId)
        ->count();

    expect($count)->toBe(1);
});
