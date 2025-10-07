<?php

declare(strict_types=1);

use App\Contracts\PaymentGatewayInterface;
use App\Models\Product;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use MasyukAI\Cart\Events\CartCleared;
use MasyukAI\Cart\Events\CartCreated;
use MasyukAI\Cart\Events\CartUpdated;
use MasyukAI\Cart\Events\ItemAdded;
use MasyukAI\Cart\Events\ItemRemoved;
use MasyukAI\Cart\Events\ItemUpdated;
use MasyukAI\Cart\Facades\Cart as CartFacade;
use MasyukAI\FilamentCart\Listeners\SyncCartItemOnAdd;
use MasyukAI\FilamentCart\Listeners\SyncCartItemOnRemove;
use MasyukAI\FilamentCart\Listeners\SyncCartItemOnUpdate;
use MasyukAI\FilamentCart\Listeners\SyncCartOnClear;
use MasyukAI\FilamentCart\Listeners\SyncCompleteCart;

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

    Event::listen(CartCreated::class, SyncCompleteCart::class);
    Event::listen(CartUpdated::class, SyncCompleteCart::class);
    Event::listen(CartCleared::class, SyncCartOnClear::class);
    Event::listen(ItemAdded::class, SyncCartItemOnAdd::class);
    Event::listen(ItemUpdated::class, SyncCartItemOnUpdate::class);
    Event::listen(ItemRemoved::class, SyncCartItemOnRemove::class);
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
