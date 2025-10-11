<?php

declare(strict_types=1);

namespace App\Console\Commands;

use AIArmada\Chip\DataObjects\ClientDetails;
use AIArmada\Chip\DataObjects\Purchase;
use AIArmada\Chip\DataObjects\PurchaseDetails;
use AIArmada\Chip\Events\PurchasePaid;
use App\Listeners\HandlePaymentSuccess;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class TestPaymentSuccessCommand extends Command
{
    protected $signature = 'test:payment-success';

    protected $description = 'Test payment success workflow with stock deduction';

    public function handle(StockService $stockService): int
    {
        $this->info('🧪 Testing Payment Success Workflow with Stock Deduction');
        $this->newLine();

        try {
            DB::transaction(function () use ($stockService) {
                // 1. Create test data
                $this->info('📝 Creating test data...');

                $user = User::firstOrCreate([
                    'email' => 'test@example.com',
                ], [
                    'name' => 'Test Customer',
                    'password' => bcrypt('password'),
                ]);

                // Create a test category
                $category = \App\Models\Category::firstOrCreate([
                    'name' => 'Test Category',
                ]);

                $product = Product::firstOrCreate([
                    'name' => 'Test Payment Product',
                    'slug' => 'test-payment-product',
                ], [
                    'description' => 'Product for testing payment workflow',
                    'category_id' => $category->id,
                    'price' => 2500, // RM 25.00
                    'is_active' => true,
                ]);

                // 2. Add stock to the product
                $this->info('📦 Adding initial stock...');
                $stockService->addStock(
                    product: $product,
                    quantity: 50,
                    reason: 'initial',
                    note: 'Initial stock for payment test',
                    userId: $user->id
                );

                $initialStock = $stockService->getCurrentStock($product);
                $this->info("📊 Initial stock: {$initialStock} units");

                // 3. Create an order
                $this->info('🛒 Creating test order...');
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'TEST-'.time(),
                    'total' => 5000, // RM 50.00 for 2 units
                    'status' => 'pending',
                    'delivery_method' => 'standard',
                ]);

                // 4. Create order items
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 2500,
                ]);

                // 5. Create payment record
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'amount' => 5000,
                    'method' => 'chip',
                    'status' => 'pending',
                    'currency' => 'MYR',
                ]);

                $this->info("✅ Order #{$order->id} created with payment #{$payment->id}");

                // 6. Simulate CHIP purchase success
                $this->info('💳 Simulating CHIP payment success...');

                // Create mock CHIP purchase object
                $mockPurchase = new Purchase(
                    id: 'purchase_test_'.time(),
                    type: 'purchase',
                    created_on: time(),
                    updated_on: time(),
                    client: ClientDetails::fromArray([
                        'full_name' => $user->name,
                        'email' => $user->email,
                        'phone' => '',
                        'personal_code' => '',
                        'legal_name' => $user->name,
                        'brand_name' => $user->name,
                        'street_address' => '',
                        'country' => 'MY',
                        'city' => '',
                        'zip_code' => '',
                        'state' => '',
                        'registration_number' => '',
                        'tax_number' => '',
                    ]),
                    purchase: PurchaseDetails::fromArray([
                        'total' => 5000,
                        'currency' => 'MYR',
                        'products' => [],
                    ]),
                    brand_id: 'test_brand',
                    payment: null,
                    issuer_details: \AIArmada\Chip\DataObjects\IssuerDetails::fromArray(['legal_name' => '']),
                    transaction_data: \AIArmada\Chip\DataObjects\TransactionData::fromArray([
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
                    reference_generated: $order->id,
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
                );

                // 7. Trigger the payment success event
                $this->info('🎯 Triggering payment success event...');
                $purchasePaidEvent = new PurchasePaid($mockPurchase);
                $listener = new HandlePaymentSuccess($stockService);
                $listener->handle($purchasePaidEvent);

                // 8. Verify results
                $this->info('🔍 Verifying results...');

                $order->refresh();
                $payment->refresh();
                $finalStock = $stockService->getCurrentStock($product);

                $this->info("📊 Final stock: {$finalStock} units (expected: ".($initialStock - 2).')');
                $this->info("📋 Order status: {$order->status}");
                $this->info(" Payment record status: {$payment->status}");

                // Check if stock transaction was created
                $stockTransaction = \App\Models\StockTransaction::where('order_item_id', $orderItem->id)
                    ->where('type', 'out')
                    ->first();

                if ($stockTransaction) {
                    $this->info("✅ Stock transaction created: ID #{$stockTransaction->id}");
                    $this->info("   - Quantity: {$stockTransaction->quantity}");
                    $this->info("   - Reason: {$stockTransaction->reason}");
                } else {
                    $this->error('❌ No stock transaction found!');
                }

                // Verify expectations
                if ($finalStock === ($initialStock - 2) &&
                    $order->status === 'confirmed' &&
                    $payment->status === 'completed' &&
                    $stockTransaction) {
                    $this->info('✅ All tests passed! Payment success workflow working correctly.');
                } else {
                    $this->error('❌ Some tests failed. Check the results above.');
                }
            });

        } catch (Exception $e) {
            $this->error('❌ Test failed with error: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 Payment success workflow test completed!');

        return Command::SUCCESS;
    }
}
