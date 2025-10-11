<?php

declare(strict_types=1);

use AIArmada\Cart\Events\CartCleared;
use AIArmada\Cart\Events\CartCreated;
use AIArmada\Cart\Events\ItemAdded;
use AIArmada\Cart\Events\ItemRemoved;
use AIArmada\Cart\Events\ItemUpdated;
use AIArmada\Cart\Facades\Cart as CartFacade;
use AIArmada\FilamentCart\Listeners\SyncCartOnEvent;
use App\Contracts\PaymentGatewayInterface;
use App\Models\Product;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->mock(PaymentGatewayInterface::class, function ($mock) {
        $mock->shouldReceive('createPurchase')->andReturn([
            'success' => true,
            'purchase_id' => 'snapshot_purchase',
            'checkout_url' => 'https://example.test/checkout',
        ]);
        $mock->shouldReceive('getPurchaseStatus')->byDefault()->andReturn(null);
    });

    Event::listen([
        CartCreated::class,
        CartCleared::class,
        ItemAdded::class,
        ItemUpdated::class,
        ItemRemoved::class,
    ], SyncCartOnEvent::class);
});

test('handlePaymentSuccess uses cart snapshot totals even when cart changes after payment intent creation', function () {
    CartFacade::clear();

    $initialProduct = Product::factory()->create(['price' => 1500]);
    CartFacade::add($initialProduct->id, $initialProduct->name, $initialProduct->price, 1);

    $cart = CartFacade::getCurrentCart();
    $cartId = $cart->getId();

    $customerData = [
        'name' => 'Checkout Snapshot',
        'email' => 'snapshot@example.com',
        'phone' => '1234567890',
        'street1' => '123 Snapshot Street',
        'city' => 'Kajang',
        'state' => 'Selangor',
        'country' => 'MY',
        'postcode' => '43000',
        'delivery_method' => 'standard',
    ];

    $paymentService = app(PaymentService::class);
    $paymentService->createPaymentIntent($cart, $customerData);

    $intent = $cart->getMetadata('payment_intent');
    expect($intent)->not->toBeNull();

    $snapshotTotal = $intent['cart_snapshot']['totals']['total'];

    $extraProduct = Product::factory()->create(['price' => 500]);
    CartFacade::add($extraProduct->id, $extraProduct->name, $extraProduct->price, 1);

    $currentCartTotal = CartFacade::total()->getAmount();
    expect($currentCartTotal)->toBeGreaterThan($snapshotTotal);

    $checkoutService = app(CheckoutService::class);

    $webhookData = [
        'event' => 'purchase.paid',
        'purchase_id' => $intent['purchase_id'],
        'amount' => $intent['amount'],
        'reference' => $cartId,
    ];

    expect($paymentService->validateCartPaymentIntent($cart)['is_valid'])->toBeFalse();
    expect($paymentService->validatePaymentWebhook($intent, $webhookData))->toBeTrue();

    $order = $checkoutService->handlePaymentSuccess($intent['purchase_id'], $webhookData);

    expect($order)->not->toBeNull();
    expect($order->total)->toBe($snapshotTotal);
    expect($order->orderItems)->toHaveCount(1);
    expect($order->orderItems->first()->product_id)->toBe($initialProduct->id);

    expect(CartFacade::isEmpty())->toBeTrue();
});

test('prepareSuccessView returns hydrated order data with cart and customer snapshots', function (): void {
    CartFacade::clear();

    $product = Product::factory()->create(['price' => 2250]);
    CartFacade::add($product->id, $product->name, $product->price, 2);

    $cart = CartFacade::getCurrentCart();
    $reference = $cart->getId();

    $customerData = [
        'name' => 'Volt Customer',
        'email' => 'volt@example.com',
        'phone' => '60123456789',
        'street1' => '1 Jalan Elektrik',
        'street2' => 'Tingkat 3',
        'city' => 'Shah Alam',
        'state' => 'Selangor',
        'country' => 'MY',
        'postcode' => '40100',
        'delivery_method' => 'express',
    ];

    $paymentService = app(PaymentService::class);
    $paymentService->createPaymentIntent($cart, $customerData);

    $intent = $cart->getMetadata('payment_intent');
    expect($intent)->not->toBeNull();

    $checkoutService = app(CheckoutService::class);

    $webhookData = [
        'event' => 'purchase.paid',
        'purchase_id' => $intent['purchase_id'],
        'amount' => $intent['amount'],
        'reference' => $reference,
    ];

    $order = $checkoutService->handlePaymentSuccess($intent['purchase_id'], $webhookData);

    expect($order)->not->toBeNull();

    $payload = $checkoutService->prepareSuccessView($reference);

    expect($payload['order'])->not->toBeNull();
    expect($payload['order']->relationLoaded('orderItems'))->toBeTrue();
    expect($payload['order']->relationLoaded('address'))->toBeTrue();
    expect($payload['payment'])->not->toBeNull();
    expect($payload['cartSnapshot'])->toBeArray();
    expect($payload['cartSnapshot']['items'] ?? [])->not->toBeEmpty();
    expect($payload['customerSnapshot'])->toMatchArray($customerData);
    expect($payload['isCompleted'])->toBeTrue();
    expect($payload['isPending'])->toBeFalse();
});
