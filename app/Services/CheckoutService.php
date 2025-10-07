<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Chip\ChipDataRecorder;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasyukAI\Cart\Cart;
use MasyukAI\Cart\Facades\Cart as CartFacade;
use Throwable;

final class CheckoutService
{
    public function __construct(
        private PaymentService $paymentService,
        private OrderService $orderService,
        private ChipDataRecorder $chipDataRecorder
    ) {}

    /**
     * Process checkout with cart metadata-based payment intents
     */
    public function processCheckout(array $customerData): array
    {
        try {
            $cart = CartFacade::getCurrentCart();

            // Validate cart has items
            if ($cart->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'Cart is empty',
                ];
            }

            // Check for existing valid payment intent
            $validation = $this->paymentService->validateCartPaymentIntent($cart);

            if ($validation['is_valid']) {
                // Reuse existing valid payment intent
                session([
                    'chip_last_purchase_id' => $validation['intent']['purchase_id'] ?? null,
                    'chip_last_reference' => $validation['intent']['customer_data']['reference'] ?? ($validation['intent']['reference'] ?? null),
                ]);

                return [
                    'success' => true,
                    'purchase_id' => $validation['intent']['purchase_id'],
                    'checkout_url' => $validation['intent']['checkout_url'],
                    'reused_intent' => true,
                ];
            }

            // Clear invalid/expired payment intent
            if (! $validation['is_valid'] && isset($validation['intent'])) {
                $this->paymentService->clearPaymentIntent($cart);
            }

            // Create new payment intent
            $result = $this->paymentService->createPaymentIntent($cart, $customerData);

            if ($result['success']) {
                session([
                    'chip_last_purchase_id' => $result['purchase_id'] ?? null,
                    'chip_last_reference' => CartFacade::getId(),
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Enhanced checkout failed', [
                'error' => $e->getMessage(),
                'customer_data' => $customerData,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle successful payment webhook and create order
     * Implements idempotency to prevent duplicate orders
     */
    public function handlePaymentSuccess(string $purchaseId, array $webhookData): ?Order
    {
        $eventName = $webhookData['event'] ?? null;
        $invocationSource = $webhookData['source']
            ?? ($eventName !== null ? 'webhook' : 'success_callback');

        Log::debug('handlePaymentSuccess invoked', [
            'purchase_id' => $purchaseId,
            'webhook_purchase_id' => $webhookData['purchase_id'] ?? $webhookData['id'] ?? null,
            'payload_keys' => array_keys($webhookData),
            'source' => $invocationSource,
            'webhook_id' => $webhookData['webhook_id'] ?? null,
        ]);

        Log::debug('Webhook reference payload', [
            'reference' => $webhookData['reference'] ?? null,
        ]);

        $purchasePayload = $webhookData['data'] ?? ($webhookData['purchase'] ?? null);

        if (is_array($purchasePayload) && isset($purchasePayload['id'])) {
            $this->chipDataRecorder->upsertPurchase($purchasePayload);
        }

        try {
            $webhookPurchaseId = $webhookData['purchase_id']
                ?? $webhookData['id']
                ?? $purchaseId;

            // Check if order already exists for this purchase (idempotency)
            $existingOrder = Order::whereHas('payments', function ($query) use ($purchaseId, $webhookPurchaseId) {
                $query->whereIn('gateway_payment_id', [$purchaseId, $webhookPurchaseId]);
            })->first();

            if ($existingOrder) {
                Log::info('Order already exists for purchase, skipping duplicate creation', [
                    'order_id' => $existingOrder->id,
                    'order_number' => $existingOrder->order_number,
                    'purchase_id' => $webhookPurchaseId,
                    'source' => $invocationSource,
                ]);

                return $existingOrder;
            }

            // Find cart with this payment intent
            $cart = $this->findCartByReference($purchaseId, $webhookData);

            if (! $cart) {
                $sessionCart = CartFacade::getCurrentCart();

                if ($sessionCart) {
                    $sessionIntent = $sessionCart->getMetadata('payment_intent');

                    if (($sessionIntent['purchase_id'] ?? null) === $purchaseId) {
                        Log::debug('Using session cart as fallback for purchase processing', [
                            'purchase_id' => $purchaseId,
                        ]);

                        $cart = $sessionCart;
                    }
                }
            }

            if (! $cart) {
                Log::warning('handlePaymentSuccess could not locate cart', [
                    'purchase_id' => $purchaseId,
                    'webhook_reference' => $webhookData['reference'] ?? null,
                    'source' => $invocationSource,
                ]);
            }
            if (! $cart) {
                Log::error('Cart not found for successful payment', [
                    'purchase_id' => $purchaseId,
                    'reference' => $webhookData['reference'] ?? null,
                    'source' => $invocationSource,
                ]);

                return null;
            }

            $paymentIntent = $cart->getMetadata('payment_intent');
            if (! $paymentIntent) {
                Log::error('Payment intent not found in cart metadata', [
                    'purchase_id' => $purchaseId,
                    'source' => $invocationSource,
                ]);

                return null;
            }

            Log::debug('Payment intent metadata loaded for purchase success', [
                'purchase_id' => $purchaseId,
                'intent_purchase_id' => $paymentIntent['purchase_id'] ?? null,
                'intent_status' => $paymentIntent['status'] ?? null,
                'reference' => data_get($paymentIntent, 'customer_data.reference', $paymentIntent['reference'] ?? null),
                'source' => $invocationSource,
            ]);

            // Validate webhook data against payment intent
            if (! $this->paymentService->validatePaymentWebhook($paymentIntent, $webhookData)) {
                Log::warning('Payment intent validation failed', [
                    'purchase_id' => $purchaseId,
                    'source' => $invocationSource,
                ]);

                return null;
            }

            Log::info('Payment payload validated against cart intent', [
                'purchase_id' => $webhookPurchaseId,
                'amount' => $paymentIntent['amount'] ?? null,
                'source' => $invocationSource,
            ]);

            $order = DB::transaction(function () use ($paymentIntent, $webhookData, $webhookPurchaseId, $invocationSource) {
                // Create order from cart snapshot
                $order = $this->createOrderFromCartSnapshot(
                    $paymentIntent['cart_snapshot'],
                    $paymentIntent['customer_data']
                );

                // Create payment record
                $payment = $this->createPaymentRecord($order, $paymentIntent, $webhookData);

                // Persist the paid status immediately once payment record exists
                $order = $this->orderService->updateOrderStatus($order, 'completed');

                Log::info('Order created successfully from cart payment intent', [
                    'order_id' => $order->id,
                    'purchase_id' => $webhookPurchaseId,
                    'amount' => $paymentIntent['amount'],
                    'order_status' => $order->status,
                    'payment_id' => $payment->id,
                    'source' => $invocationSource,
                ]);

                return $order;
            });

            // Clear cart AFTER transaction commits successfully
            // This ensures cart is cleared even if there are minor issues later
            // And cart remains available if transaction fails
            try {
                $cart->clear();
                Log::info('Cart cleared successfully after order creation', [
                    'order_id' => $order->id,
                    'purchase_id' => $webhookPurchaseId,
                    'source' => $invocationSource,
                ]);
            } catch (Exception $e) {
                Log::error('Failed to clear cart after order creation', [
                    'order_id' => $order->id,
                    'purchase_id' => $webhookPurchaseId,
                    'error' => $e->getMessage(),
                    'source' => $invocationSource,
                ]);
                // Don't fail the order creation if cart clearing fails
            }

            Log::debug('handlePaymentSuccess completed successfully', [
                'purchase_id' => $webhookPurchaseId,
                'order_id' => $order->id,
                'source' => $invocationSource,
            ]);

            return $order;

        } catch (Exception $e) {
            Log::error('Failed to handle payment success', [
                'purchase_id' => $purchaseId,
                'error' => $e->getMessage(),
                'payload_keys' => array_keys($webhookData),
                'source' => $invocationSource,
            ]);

            return null;
        }
    }

    /**
     * Get cart change validation for UI display
     */
    public function getCartChangeStatus(): array
    {
        $cart = CartFacade::getCurrentCart();
        $validation = $this->paymentService->validateCartPaymentIntent($cart);

        return [
            'is_valid' => $validation['is_valid'],
            'cart_changed' => $validation['cart_changed'] ?? false,
            'intent' => $validation['intent'] ?? null,
            'has_active_intent' => $validation['has_active_intent'] ?? false,
        ];
    }

    /**
     * Find cart by reference (cart ID) from webhook data or fallback to CHIP API
     * Uses direct primary key lookup - much faster than JSONB query
     */
    /**
     * Resolve the data required by the checkout success page.
     *
     * @return array{order:?Order,payment:?Payment,reference:string,isCompleted:bool,isPending:bool}
     */
    public function prepareSuccessView(string $reference): array
    {
        $order = null;
        $payment = null;
        $cartSnapshot = null;
        $customerSnapshot = null;

        Log::debug('=== prepareSuccessView START ===', [
            'reference' => $reference,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Step 1: Lookup cart data
        Log::debug('Step 1: Looking up cart data', [
            'reference' => $reference,
        ]);

        $cartData = DB::table('carts')->where('id', $reference)->first();

        if (! $cartData) {
            Log::warning('Cart data not found in database', [
                'reference' => $reference,
            ]);
        } else {
            Log::debug('Cart data found', [
                'reference' => $reference,
                'cart_id' => $cartData->id,
                'instance' => $cartData->instance,
                'identifier' => $cartData->identifier,
                'has_metadata' => ! empty($cartData->metadata),
                'metadata_length' => $cartData->metadata ? mb_strlen($cartData->metadata) : 0,
            ]);
        }

        if ($cartData && $cartData->metadata) {
            Log::debug('Step 2: Parsing cart metadata', [
                'reference' => $reference,
            ]);

            $metadata = json_decode($cartData->metadata, true) ?: [];

            Log::debug('Metadata decoded', [
                'reference' => $reference,
                'metadata_keys' => array_keys($metadata),
                'has_payment_intent' => isset($metadata['payment_intent']),
            ]);

            $paymentIntent = $metadata['payment_intent'] ?? null;
            $cartSnapshot = $paymentIntent['cart_snapshot'] ?? null;
            $customerSnapshot = $paymentIntent['customer_data'] ?? null;

            if (! $paymentIntent) {
                Log::warning('Payment intent not found in cart metadata', [
                    'reference' => $reference,
                    'available_keys' => array_keys($metadata),
                ]);
            } else {
                Log::debug('Payment intent found in metadata', [
                    'reference' => $reference,
                    'intent_keys' => array_keys($paymentIntent),
                    'purchase_id' => $paymentIntent['purchase_id'] ?? null,
                    'status' => $paymentIntent['status'] ?? null,
                    'amount' => $paymentIntent['amount'] ?? null,
                    'created_at' => $paymentIntent['created_at'] ?? null,
                ]);
            }

            if ($paymentIntent && isset($paymentIntent['purchase_id'])) {
                $purchaseId = $paymentIntent['purchase_id'];

                Log::debug('Step 3: Looking up existing payment record', [
                    'purchase_id' => $purchaseId,
                    'reference' => $reference,
                ]);

                $payment = Payment::where('gateway_payment_id', $purchaseId)->first();

                if ($payment) {
                    Log::debug('Existing payment record found', [
                        'payment_id' => $payment->id,
                        'purchase_id' => $purchaseId,
                        'payment_status' => $payment->status,
                        'payment_amount' => $payment->amount,
                        'order_id' => $payment->order_id,
                        'paid_at' => $payment->paid_at?->toIso8601String(),
                    ]);

                    $order = $payment?->order;

                    if ($order) {
                        Log::debug('Existing order found via payment', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'order_total' => $order->total,
                            'order_status' => $order->status,
                        ]);
                    }
                } else {
                    Log::info('No existing payment found, will check CHIP API', [
                        'purchase_id' => $purchaseId,
                        'reference' => $reference,
                    ]);
                }

                if (! $order) {
                    Log::debug('Step 4: Order not found, fetching from CHIP API', [
                        'purchase_id' => $purchaseId,
                        'reference' => $reference,
                    ]);

                    try {
                        // Ask CHIP for the definitive purchase snapshot so we can validate and store it.
                        Log::debug('Calling CHIP API getPurchaseStatus', [
                            'purchase_id' => $purchaseId,
                        ]);

                        $chipPurchase = $this->paymentService->getPurchaseStatus($purchaseId);

                        if (! is_array($chipPurchase)) {
                            Log::warning('CHIP API returned non-array response', [
                                'purchase_id' => $purchaseId,
                                'response_type' => gettype($chipPurchase),
                            ]);
                        } else {
                            Log::debug('CHIP API response received', [
                                'purchase_id' => $purchaseId,
                                'chip_status' => $chipPurchase['status'] ?? null,
                                'chip_id' => $chipPurchase['id'] ?? null,
                                'chip_reference' => $chipPurchase['reference'] ?? null,
                                'chip_amount' => $chipPurchase['purchase']['total'] ?? null,
                                'chip_currency' => $chipPurchase['purchase']['currency'] ?? null,
                                'has_payment' => isset($chipPurchase['payment']),
                                'response_keys' => array_keys($chipPurchase),
                            ]);
                        }

                        if (is_array($chipPurchase) && ($chipPurchase['status'] ?? null) === 'paid') {
                            Log::info('CHIP purchase is PAID, proceeding with order creation', [
                                'purchase_id' => $purchaseId,
                                'chip_status' => $chipPurchase['status'],
                            ]);

                            Log::debug('Upserting CHIP purchase data', [
                                'purchase_id' => $purchaseId,
                            ]);

                            $this->chipDataRecorder->upsertPurchase($chipPurchase);

                            Log::debug('CHIP purchase upserted successfully', [
                                'purchase_id' => $purchaseId,
                            ]);

                            $normalizedPayload = [
                                'event' => 'purchase.paid',
                                'purchase_id' => $chipPurchase['id'] ?? $purchaseId,
                                'id' => $chipPurchase['id'] ?? $purchaseId,
                                'reference' => $chipPurchase['reference']
                                    ?? ($paymentIntent['customer_data']['reference'] ?? $reference),
                                'amount' => $paymentIntent['amount'] ?? null,
                                'payment' => $chipPurchase['payment'] ?? null,
                                'data' => $chipPurchase,
                                'source' => 'success_callback',
                            ];

                            Log::debug('Normalized payload prepared for handlePaymentSuccess', [
                                'purchase_id' => $purchaseId,
                                'payload_keys' => array_keys($normalizedPayload),
                                'normalized_reference' => $normalizedPayload['reference'],
                                'normalized_amount' => $normalizedPayload['amount'],
                            ]);

                            Log::debug('Calling handlePaymentSuccess', [
                                'purchase_id' => $purchaseId,
                            ]);

                            $order = $this->handlePaymentSuccess($purchaseId, $normalizedPayload);

                            if ($order) {
                                Log::info('Order created successfully via success callback', [
                                    'order_id' => $order->id,
                                    'order_number' => $order->order_number,
                                    'purchase_id' => $purchaseId,
                                ]);

                                $payment = $order?->payments()->latest()->first();

                                if ($payment) {
                                    Log::debug('Payment record retrieved from new order', [
                                        'payment_id' => $payment->id,
                                        'payment_status' => $payment->status,
                                    ]);
                                }
                            } else {
                                Log::warning('handlePaymentSuccess returned null', [
                                    'purchase_id' => $purchaseId,
                                ]);
                            }
                        } else {
                            Log::warning('CHIP purchase not paid, cannot create order', [
                                'purchase_id' => $purchaseId,
                                'chip_status' => $chipPurchase['status'] ?? 'unknown',
                                'is_array' => is_array($chipPurchase),
                            ]);
                        }
                    } catch (Throwable $throwable) {
                        Log::error('Exception while processing CHIP purchase', [
                            'purchase_id' => $purchaseId,
                            'reference' => $reference,
                            'error' => $throwable->getMessage(),
                            'exception_class' => get_class($throwable),
                            'file' => $throwable->getFile(),
                            'line' => $throwable->getLine(),
                            'trace' => $throwable->getTraceAsString(),
                        ]);
                    }
                }
            } else {
                Log::warning('Payment intent missing purchase_id', [
                    'reference' => $reference,
                    'has_payment_intent' => $paymentIntent !== null,
                    'intent_keys' => $paymentIntent ? array_keys($paymentIntent) : [],
                ]);
            }
        } else {
            Log::debug('Cart data or metadata not available', [
                'reference' => $reference,
                'has_cart_data' => $cartData !== null,
                'has_metadata' => $cartData ? ! empty($cartData->metadata) : false,
            ]);
        }

        // Step 5: Fallback payment lookup
        if (! $payment) {
            Log::debug('Step 5: Payment not found via purchase_id, trying reference fallback', [
                'reference' => $reference,
            ]);

            $payment = Payment::where('reference', $reference)->latest()->first();

            if ($payment) {
                Log::debug('Payment found via reference fallback', [
                    'payment_id' => $payment->id,
                    'payment_status' => $payment->status,
                    'gateway_payment_id' => $payment->gateway_payment_id,
                    'order_id' => $payment->order_id,
                ]);

                $order = $payment?->order;

                if ($order) {
                    Log::debug('Order retrieved via fallback payment', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                    ]);
                }
            } else {
                Log::warning('No payment found via reference fallback', [
                    'reference' => $reference,
                ]);
            }
        }

        if ($order) {
            $order->loadMissing(['orderItems.product', 'address', 'user']);
            $payment = $payment ?? $order->payments()->latest()->first();
        }

        if (! $cartSnapshot && $order) {
            $cartSnapshot = [
                'items' => $order->cart_items ?? [],
                'totals' => [
                    'total' => $order->total,
                ],
            ];
        }

        if (! $customerSnapshot && $order) {
            $customerSnapshot = $order->checkout_form_data ?? null;
        }

        $isCompleted = $payment !== null && $payment->status === 'completed';
        $isPending = $order === null;

        Log::debug('=== prepareSuccessView END ===', [
            'reference' => $reference,
            'order_id' => $order?->id,
            'order_number' => $order?->order_number,
            'payment_id' => $payment?->id,
            'payment_status' => $payment?->status,
            'is_completed' => $isCompleted,
            'is_pending' => $isPending,
            'timestamp' => now()->toIso8601String(),
        ]);

        return [
            'order' => $order,
            'payment' => $payment,
            'reference' => $reference,
            'cartSnapshot' => $cartSnapshot,
            'customerSnapshot' => $customerSnapshot,
            'isCompleted' => $isCompleted,
            'isPending' => $isPending,
        ];
    }

    /**
     * Create order from cart snapshot stored in payment intent
     *
     * Cart snapshot structure:
     * [
     *   'items' => [...],
     *   'conditions' => [...],
     *   'totals' => ['subtotal' => 0, 'total' => 0, 'savings' => 0]
     * ]
     */
    private function createOrderFromCartSnapshot(array $cartSnapshot, array $customerData): Order
    {
        // Create or find user
        $user = $this->createOrFindUser($customerData);

        // Create address record
        $address = $this->createAddress($user, $customerData);

        // Extract items from snapshot (handles both old and new format)
        $cartItems = $cartSnapshot['items'] ?? $cartSnapshot;

        // Create order using OrderService with cart items
        return $this->orderService->createOrder($user, $address, $customerData, $cartItems, $cartSnapshot);
    }

    /**
     * Create payment record for completed order
     */
    private function createPaymentRecord(Order $order, array $paymentIntent, array $webhookData): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total, // Use order total, not payment intent amount
            'status' => 'completed',
            'method' => 'chip',
            'currency' => 'MYR',
            'gateway_payment_id' => $paymentIntent['purchase_id'],
            'gateway_response' => $webhookData,
            'reference' => $paymentIntent['customer_data']['reference']
                ?? ($paymentIntent['reference'] ?? null),
            'paid_at' => now(),
        ]);
    }

    private function findCartByReference(string $purchaseId, array $webhookData): ?Cart
    {
        // Try to get cart reference from webhook data first (faster)
        $cartId = $webhookData['reference'] ?? null;

        // Fallback: fetch from CHIP API if reference not in webhook
        if (! $cartId) {
            Log::info('Reference not in webhook, attempting local metadata lookup', [
                'purchase_id' => $purchaseId,
            ]);

            $cartCandidates = DB::table('carts')
                ->whereNotNull('metadata')
                ->get();

            $matchingCart = null;

            foreach ($cartCandidates as $candidate) {
                $metadata = json_decode($candidate->metadata ?? '', true) ?: [];
                $intent = $metadata['payment_intent'] ?? [];

                if (($intent['purchase_id'] ?? null) === $purchaseId) {
                    $matchingCart = $candidate;
                    break;
                }
            }

            if ($matchingCart) {
                Log::debug('Cart located via metadata scan', [
                    'cart_id' => $matchingCart->id,
                    'purchase_id' => $purchaseId,
                ]);

                $cartId = $matchingCart->id;
            } else {
                Log::debug('Cart metadata scan did not match purchase id', [
                    'purchase_id' => $purchaseId,
                    'scanned_carts' => $cartCandidates->count(),
                ]);
            }
        }

        if (! $cartId) {
            Log::info('Reference not in webhook, fetching from CHIP API', [
                'purchase_id' => $purchaseId,
            ]);

            $purchaseStatus = $this->paymentService->getPurchaseStatus($purchaseId);

            if (! $purchaseStatus || ! isset($purchaseStatus['reference'])) {
                Log::warning('Purchase status missing or no reference found', [
                    'purchase_id' => $purchaseId,
                ]);

                return null;
            }

            Log::debug('Purchase status retrieved for cart lookup', [
                'purchase_id' => $purchaseId,
                'status' => $purchaseStatus['status'] ?? null,
                'reference' => $purchaseStatus['reference'],
            ]);

            $cartId = $purchaseStatus['reference'];
        }

        // Direct primary key lookup - blazing fast!
        $cartData = DB::table('carts')->where('id', $cartId)->first();

        if (! $cartData) {
            Log::warning('Cart not found for reference', [
                'purchase_id' => $purchaseId,
                'cart_id' => $cartId,
            ]);

            return null;
        }

        Log::debug('Cart located for payment success', [
            'cart_id' => $cartId,
            'instance' => $cartData->instance,
            'identifier' => $cartData->identifier,
            'metadata_present' => ! empty($cartData->metadata),
        ]);

        // Get CartManager and reconstruct Cart instance from database
        $cartManager = app(\MasyukAI\Cart\CartManager::class);

        return $cartManager->getCartInstance(
            $cartData->instance,
            $cartData->identifier
        );
    }

    /**
     * Create or find user record
     */
    private function createOrFindUser(array $customerData): User
    {
        $user = User::where('email', $customerData['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => $customerData['name'] ?? null,
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? null,
                'is_guest' => true,
            ]);
        }

        return $user;
    }

    /**
     * Create address record using polymorphic relationship
     */
    private function createAddress(User $user, array $customerData): Address
    {
        return Address::create([
            // Polymorphic relationship fields (addresses table uses addressable)
            'addressable_type' => User::class,
            'addressable_id' => $user->id,

            // Address fields matching database schema
            'name' => $customerData['name'],
            'company' => $customerData['company'] ?? null,
            'street1' => $customerData['street1'],
            'street2' => $customerData['street2'] ?? null,
            'city' => $customerData['city'] ?? null,
            'state' => $customerData['state'],
            'country' => $customerData['country'],
            'postcode' => $customerData['postcode'],
            'phone' => $customerData['phone'],
            'type' => $customerData['type'] ?? 'shipping',
            'is_primary' => true,
        ]);
    }
}
