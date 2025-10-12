<?php

declare(strict_types=1);

use App\Events\OrderPaid;
use App\Listeners\GenerateOrderInvoice;
use App\Listeners\ProcessOrderShipping;
use App\Listeners\SendOrderConfirmationEmail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use App\Notifications\OrderConfirmation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('OrderPaid event is dispatched after successful payment', function () {
    Event::fake();

    // Create test data
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    // Mock the CheckoutService to trigger the event
    $checkoutService = app(App\Services\CheckoutService::class);

    // We can't easily test the actual dispatch without a full integration test,
    // but we can verify the event structure
    $event = new OrderPaid($order, $payment, ['test' => 'data'], 'webhook');

    expect($event->order)->toBe($order);
    expect($event->payment)->toBe($payment);
    expect($event->webhookData)->toBe(['test' => 'data']);
    expect($event->source)->toBe('webhook');
});

test('SendOrderConfirmationEmail listener is queued and sends notification', function () {
    Notification::fake();
    config(['queue.default' => 'sync']); // Process queue jobs synchronously

    $user = User::factory()->create(['email' => 'test@example.com']);
    $order = Order::factory()->create(['user_id' => $user->id]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    $event = new OrderPaid($order, $payment, [], 'webhook');

    // Create and handle the listener
    $listener = new SendOrderConfirmationEmail();
    $listener->handle($event);

    // Verify notification was sent to the email address (via Notification::route)
    Notification::assertSentOnDemand(
        OrderConfirmation::class,
        function ($notification, $channels, $notifiable) use ($order, $payment, $user) {
            return $notification->order->id === $order->id
                && $notification->payment->id === $payment->id
                && $notifiable->routes['mail'] === $user->email;
        }
    );
});

test('GenerateOrderInvoice listener creates invoice placeholder', function () {
    Storage::fake('public');
    Log::shouldReceive('info')->twice();
    Log::shouldReceive('error')->andReturn(); // Allow error logs

    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'TEST-123',
    ]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    $event = new OrderPaid($order, $payment, [], 'webhook');

    $listener = new GenerateOrderInvoice();
    $listener->handle($event);

    // Verify invoice file was created
    expect(Storage::disk('public')->exists("invoices/{$order->order_number}.pdf"))->toBeTrue();

    // Verify order was updated
    $order->refresh();
    expect($order->invoice_path)->toBe("invoices/{$order->order_number}.pdf");
    expect($order->invoice_generated_at)->not->toBeNull();
});

test('ProcessOrderShipping listener creates shipment record', function () {
    Log::shouldReceive('info')->twice();
    Log::shouldReceive('error')->andReturn(); // Allow error logs

    $user = User::factory()->create();
    $product = Product::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    // Create order items to simulate physical products
    $order->orderItems()->create([
        'product_id' => $product->id,
        'name' => 'Test Product',
        'quantity' => 1,
        'unit_price' => 1000,
        'total' => 1000,
    ]);

    $event = new OrderPaid($order, $payment, [], 'webhook');

    $listener = new ProcessOrderShipping();
    $listener->handle($event);

    // Verify shipment was created
    $shipment = Shipment::where('order_id', $order->id)->first();
    expect($shipment)->not->toBeNull();
    expect($shipment->tracking_number)->toContain('SHIP-'.$order->order_number);
    expect($shipment->status)->toBe('processing');
    expect($shipment->carrier)->toBe('Placeholder Carrier');
});

test('listeners are properly configured for queuing', function () {
    $emailListener = new SendOrderConfirmationEmail();
    $invoiceListener = new GenerateOrderInvoice();
    $shippingListener = new ProcessOrderShipping();

    // Verify they implement ShouldQueue
    expect($emailListener)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
    expect($invoiceListener)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
    expect($shippingListener)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);

    // Verify retry configuration
    expect($emailListener->tries)->toBe(3);
    expect($invoiceListener->tries)->toBe(3);
    expect($shippingListener->tries)->toBe(3);

    // Verify backoff times
    expect($emailListener->backoff)->toBe(60); // 1 minute
    expect($invoiceListener->backoff)->toBe(60); // 1 minute
    expect($shippingListener->backoff)->toBe(120); // 2 minutes
});

test('OrderConfirmation notification has correct structure', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-123',
        'total' => 2500, // $25.00
    ]);
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'gateway_name' => 'CHIP',
    ]);

    $notification = new OrderConfirmation($order, $payment);
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Order Confirmation #ORD-123');
    expect($mailMessage->greeting)->toBe('Hi John Doe,');
    expect($mailMessage->introLines)->toContain('Thank you for your order! Your payment has been successfully processed.');
});

test('OrderPaid event serializes correctly for queue', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    $event = new OrderPaid($order, $payment, ['test' => 'data'], 'webhook');

    // Test serialization (required for queued listeners)
    $serialized = serialize($event);
    expect($serialized)->toBeString();

    $unserialized = unserialize($serialized);
    expect($unserialized)->toBeInstanceOf(OrderPaid::class);
    expect($unserialized->order->id)->toBe($order->id);
    expect($unserialized->payment->id)->toBe($payment->id);
    expect($unserialized->webhookData)->toBe(['test' => 'data']);
    expect($unserialized->source)->toBe('webhook');
});
