<?php

declare(strict_types=1);

use App\Services\CheckoutService;
use Illuminate\Support\Facades\Route;

/**
 * Development routes for testing webhooks locally
 * Only available when APP_ENV=local
 */
if (app()->environment('local')) {
    Route::prefix('dev')->group(function () {

        /**
         * Simulate CHIP payment success webhook
         *
         * Usage:
         * POST /dev/webhook/chip/simulate-success
         * Body: {"purchase_id": "test_purchase_123"}
         */
        Route::post('/webhook/chip/simulate-success', function () {
            $purchaseId = request('purchase_id');

            if (! $purchaseId) {
                return response()->json(['error' => 'purchase_id required'], 400);
            }

            // Simulate CHIP webhook payload
            $webhookData = [
                'id' => $purchaseId,
                'status' => 'paid',
                'purchase' => [
                    'id' => $purchaseId,
                    'amount' => 4500, // MYR 45.00
                    'currency' => 'MYR',
                    'status' => 'paid',
                    'client' => [
                        'email' => 'test@example.com',
                    ],
                ],
                'payment_method' => 'fpx_b2c',
                'paid_at' => now()->toISOString(),
            ];

            // Process webhook
            $checkoutService = app(CheckoutService::class);
            $order = $checkoutService->handlePaymentSuccess($purchaseId, $webhookData);

            if ($order) {
                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => 'Order created successfully from simulated webhook',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
            ], 500);
        });

        /**
         * Simulate CHIP payment failure webhook
         */
        Route::post('/webhook/chip/simulate-failure', function () {
            $purchaseId = request('purchase_id');

            $webhookData = [
                'id' => $purchaseId,
                'status' => 'failed',
                'purchase' => [
                    'id' => $purchaseId,
                    'status' => 'failed',
                    'failure_reason' => 'Insufficient funds',
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Payment failed webhook simulated',
                'data' => $webhookData,
            ]);
        });

        /**
         * View cart metadata for debugging
         */
        Route::get('/cart/metadata', function () {
            $sessionId = session()->getId();
            $cart = AIArmada\Cart\Facades\Cart::getCurrentCart();

            return response()->json([
                'identifier' => $cart->getIdentifier(),
                'session_id' => $sessionId,
                'same_as_session' => $sessionId === $cart->getIdentifier(),
                'instance' => $cart->instance(),
                'payment_intent' => $cart->getMetadata('payment_intent'),
                'items' => $cart->getItems()->toArray(),
                'total' => $cart->getRawTotal(),
            ]);
        });
    });
}
