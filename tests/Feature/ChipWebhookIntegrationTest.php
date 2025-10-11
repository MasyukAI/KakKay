<?php

declare(strict_types=1);

use AIArmada\Cart\Facades\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderCreationFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutExceptionHandling();
    Notification::fake();
    config(['chip.webhooks.verify_signature' => false]);
    config(['chip.webhooks.company_public_key' => 'test-public-key']);
});

it('persists purchases and creates order on purchase.paid webhook', function (): void {
    $product = Product::factory()->create(['price' => 1500]);

    Cart::clear();
    Cart::add($product->id, $product->name, 1500, 1);

    $cart = Cart::getCurrentCart();
    $cartId = $cart->getId();
    $purchaseId = 'purchase-'.$cartId;

    $customerData = [
        'name' => 'Webhook Customer',
        'email' => 'webhook@example.com',
        'phone' => '60123456789',
        'street1' => 'Webhook Street',
        'city' => 'Cyberjaya',
        'state' => 'Selangor',
        'country' => 'MY',
        'postcode' => '63000',
        'delivery_method' => 'standard',
    ];

    $cart->setMetadata('payment_intent', [
        'purchase_id' => $purchaseId,
        'amount' => 1500,
        'cart_version' => $cart->getVersion(),
        'cart_snapshot' => [
            'items' => $cart->getItems()->toArray(),
            'conditions' => [],
            'totals' => [
                'subtotal' => 1500,
                'subtotal_without_conditions' => 1500,
                'total' => 1500,
                'savings' => 0,
            ],
        ],
        'customer_data' => $customerData,
        'created_at' => now()->toISOString(),
        'status' => 'created',
        'checkout_url' => 'https://example.test/checkout/'.$cartId,
        'reference' => $cartId,
    ]);

    $payload = [
        'event' => 'purchase.paid',
        'data' => [
            'id' => $purchaseId,
            'status' => 'paid',
            'amount' => 1500,
            'currency' => 'MYR',
            'reference' => $cartId,
            'payment' => [
                'id' => 'payment_'.$purchaseId,
                'payment_type' => 'purchase',
                'is_outgoing' => false,
                'amount' => 1500,
                'currency' => 'MYR',
                'net_amount' => 1500,
                'fee_amount' => 0,
                'pending_amount' => 0,
            ],
            'transaction_data' => [
                'payment_method' => 'fpx_b2c',
                'country' => 'MY',
                'attempts' => [],
            ],
            'purchase' => [
                'currency' => 'MYR',
                'products' => [[
                    'name' => $product->name,
                    'quantity' => 1,
                    'price' => 1500,
                    'discount' => 0,
                    'tax_percent' => 0,
                    'category' => 'test',
                ]],
                'total' => 1500,
                'request_client_details' => [],
            ],
            'client' => [
                'email' => 'webhook@example.com',
                'full_name' => 'Webhook Customer',
                'phone' => '60123456789',
            ],
        ],
    ];

    $response = postJson('/webhooks/chip', $payload, [
        'X-Signature' => 'testing-signature',
    ]);

    $response->assertOk();

    $order = Order::whereHas('payments', function ($query) use ($purchaseId) {
        $query->where('gateway_payment_id', $purchaseId);
    })->first();

    expect($order)->not->toBeNull();
    expect($order->total)->toBe(1500);
    expect($order->orderItems)->toHaveCount(1);

    $webhookRow = DB::table('chip_webhooks')->where('id', $payload['data']['id'])->first();
    expect($webhookRow)->not->toBeNull();
    expect((bool) $webhookRow->processed)->toBeTrue();

    $purchaseRow = DB::table('chip_purchases')->where('id', $purchaseId)->first();
    expect($purchaseRow)->not->toBeNull();
    expect(json_decode($purchaseRow->client, true)['email'])->toBe('webhook@example.com');
});

it('notifies team when a purchase payment fails', function (): void {
    $purchaseId = 'purchase-failure-1';

    $payload = [
        'event' => 'purchase.payment_failure',
        'data' => [
            'id' => $purchaseId,
            'amount' => 500,
            'currency' => 'MYR',
            'failure_reason' => 'Payment declined by issuer',
            'transaction_data' => [
                'payment_method' => 'card',
            ],
        ],
    ];

    $response = postJson('/webhooks/chip', $payload, [
        'X-Signature' => 'testing-signature',
    ]);

    $response->assertOk();

    Notification::assertSentOnDemand(OrderCreationFailed::class, function (OrderCreationFailed $notification, array $channels, $notifiable) use ($purchaseId) {
        return in_array('mail', $channels, true)
            && $notification->toArray($notifiable)['purchase_id'] === $purchaseId;
    });
});
