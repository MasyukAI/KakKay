<?php

declare(strict_types=1);

use AIArmada\Chip\Services\WebhookService;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->mock(WebhookService::class, function ($mock): void {
        $mock->shouldReceive('getPublicKey')
            ->with('wh_test')
            ->andReturn('pem-key');
        $mock->shouldReceive('verifySignature')
            ->andReturn(true);
    });
});

test('payment failure webhook marks payment and order as failed', function (): void {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 4999,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 4999,
    ]);

    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'status' => 'pending',
        'amount' => 4999,
        'currency' => 'MYR',
        'method' => 'chip',
        'gateway_payment_id' => 'purchase-failure-123',
    ]);

    // CHIP webhook payload format: nested data structure
    $response = $this->postJson('/webhooks/chip/wh_test', [
        'event' => 'purchase.payment_failure',
        'data' => [
            'id' => 'purchase-failure-123',
            'failure_reason' => 'Insufficient funds',
            'payment' => [
                'id' => 'payment-failure-123',
            ],
            'transaction_data' => [
                'payment_method' => 'fpx',
            ],
        ],
    ], [
        'X-Signature' => 'test-signature',
    ]);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe('failed');
    expect($payment->gateway_transaction_id)->toBe('payment-failure-123');
    expect($payment->note)->toBe('Insufficient funds');
    expect($payment->failed_at)->not->toBeNull();
    expect($payment->gateway_response['failure_reason'] ?? null)->toBe('Insufficient funds');

    expect($order->status)->toBe('failed');
    expect($order->statusHistories()->count())->toBe(1);

    $history = $order->statusHistories()->latest()->first();

    expect($history)->not->toBeNull();
    expect($history->from_status)->toBe('pending');
    expect($history->to_status)->toBe('failed');
    expect($history->actor_type)->toBe('gateway');
    expect($history->meta['gateway'] ?? null)->toBe('chip');
    expect($history->meta['purchase_id'] ?? null)->toBe('purchase-failure-123');
    expect($history->note)->toContain('Insufficient funds');
});
