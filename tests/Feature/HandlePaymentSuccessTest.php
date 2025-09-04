<?php

use App\Listeners\HandlePaymentSuccess;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Masyukai\Chip\Events\PurchasePaid;
use MasyukAI\Cart\Facades\Cart;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the purchase object that would come from the Chip payment gateway
    $this->purchase = new \stdClass();
    $this->purchase->id = 'purchase_123456';
    $this->purchase->reference = null; // Will be set in tests
    $this->purchase->amountInCents = 10000; // RM 100.00
    $this->purchase->purchase = new \stdClass();
    $this->purchase->purchase->total = 10000;

    // Mock the StockService
    $this->stockService = Mockery::mock(StockService::class);
    $this->stockService->shouldReceive('recordSale')->andReturn(new \App\Models\StockTransaction());
    
    $this->listener = new HandlePaymentSuccess($this->stockService);
});

test('cart is cleared after successful payment', function () {
    // Create a user
    $user = User::factory()->create();
    
    // Create a product
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 10000, // RM 100.00
    ]);
    
    // Create an order
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => 10000, // RM 100.00
    ]);
    
    // Create an order item
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 10000, // RM 100.00
    ]);
    
    // Create a payment
    Payment::create([
        'order_id' => $order->id,
        'status' => 'pending',
        'amount' => 10000, // RM 100.00
        'gateway_payment_id' => 'purchase_123456',
        'method' => 'chip',
        'currency' => 'MYR',
    ]);
    
    // Add items to cart (to simulate a user with items in cart)
    Cart::add(
        (string) $product->id,
        $product->name,
        $product->price,
        1
    );
    
    // Verify cart has items
    expect(Cart::getTotalQuantity())->toBe(1);
    
    // Set the reference to the order ID
    $this->purchase->reference = $order->id;
    
    // Create the event
    $event = new PurchasePaid($this->purchase);
    
    // Handle the event
    $this->listener->handle($event);
    
    // Assert order status updated
    expect($order->fresh()->status)->toBe('confirmed');
    
    // Assert payment status updated
    expect($order->payments->first()->status)->toBe('completed');
    
    // Assert order status history created
    $statusHistory = $order->statusHistories()->latest()->first();
    expect($statusHistory)->not->toBeNull();
    expect($statusHistory->from_status)->toBe('pending');
    expect($statusHistory->to_status)->toBe('confirmed');
    expect($statusHistory->actor_type)->toBe('system');
    
    // Assert cart is cleared
    expect(Cart::getTotalQuantity())->toBe(0);
    expect(count(Cart::getContent()))->toBe(0);
});
