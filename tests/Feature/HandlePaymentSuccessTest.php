<?php

declare(strict_types=1);

use App\Listeners\HandlePaymentSuccess;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Chip\DataObjects\ClientDetails;
use MasyukAI\Chip\DataObjects\Purchase;
use MasyukAI\Chip\DataObjects\PurchaseDetails;
use MasyukAI\Chip\Events\PurchasePaid;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock the StockService
    $this->stockService = Mockery::mock(StockService::class);
    $this->stockService->shouldReceive('recordSale')->andReturn(new App\Models\StockTransaction);

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

    // Create Purchase object with order reference
    $purchase = new Purchase(
        id: 'purchase_test_'.time(),
        type: 'purchase',
        created_on: time(),
        updated_on: time(),
        client: ClientDetails::fromArray([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+60123456789',
            'personal_code' => '',
            'legal_name' => 'Test User',
            'brand_name' => 'Test User',
            'street_address' => '',
            'country' => 'MY',
            'city' => '',
            'zip_code' => '',
            'state' => '',
            'registration_number' => '',
            'tax_number' => '',
        ]),
        purchase: PurchaseDetails::fromArray([
            'total' => 10000,
            'currency' => 'MYR',
            'products' => [],
        ]),
        brand_id: 'test_brand',
        payment: null,
        issuer_details: MasyukAI\Chip\DataObjects\IssuerDetails::fromArray(['legal_name' => 'Test Bank']),
        transaction_data: MasyukAI\Chip\DataObjects\TransactionData::fromArray([
            'payment_method' => 'fpx',
            'extra' => [],
            'country' => 'MY',
            'attempts' => [],
        ]),
        status: 'paid',
        status_history: [],
        viewed_on: null,
        company_id: 'test_company',
        is_test: true,
        user_id: null,
        billing_template_id: null,
        client_id: null,
        send_receipt: false,
        is_recurring_token: false,
        recurring_token: null,
        skip_capture: false,
        force_recurring: false,
        reference_generated: (string) $order->id,
        reference: (string) $order->id,
        notes: null,
        issued: null,
        due: null,
        refund_availability: 'all',
        refundable_amount: 0,
        currency_conversion: null,
        payment_method_whitelist: [],
        success_redirect: null,
        failure_redirect: null,
        cancel_redirect: null,
        success_callback: null,
        creator_agent: null,
        platform: 'api',
        product: 'test',
        created_from_ip: null,
        invoice_url: null,
        checkout_url: null,
        direct_post_url: null,
        marked_as_paid: false,
        order_id: null,
        upsell_campaigns: [],
        referral_campaign_id: null,
        referral_code: null,
        referral_code_details: null,
        referral_code_generated: null,
        retain_level_details: null,
        can_retrieve: true,
        can_chargeback: false,
    );

    // Create the event
    $event = new PurchasePaid($purchase);

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
});
