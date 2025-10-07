<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use MasyukAI\Cart\Facades\Cart;

final class CheckoutController extends Controller
{
    /**
     * Show checkout success page
     *
     * Uses cart reference from URL to locate the cart:
     * 1. Try to create order immediately from cart metadata (better UX)
     * 2. If webhook already created it, use existing order
     * 3. Webhook serves as fallback and verification
     */
    public function success(Request $request, string $reference): View
    {
        $order = null;
        $payment = null;

        Log::debug('Checkout success redirect hit', [
            'reference' => $reference,
        ]);

        // Find cart by reference (cart ID) from database
        $cartData = DB::table('carts')->where('id', $reference)->first();

        if ($cartData && $cartData->metadata) {
            $metadata = json_decode($cartData->metadata, true);
            $paymentIntent = $metadata['payment_intent'] ?? null;

            if ($paymentIntent && isset($paymentIntent['purchase_id'])) {
                $purchaseId = $paymentIntent['purchase_id'];

                Log::debug('Checkout redirect found cart payment intent', [
                    'purchase_id' => $purchaseId,
                    'cart_version' => $paymentIntent['cart_version'] ?? null,
                    'intent_status' => $paymentIntent['status'] ?? null,
                ]);

                // First, check if webhook already created the order
                $payment = Payment::where('gateway_payment_id', $purchaseId)->first();
                $order = $payment?->order;

                // If no order yet, try to create it immediately (better UX!)
                if (! $order) {
                    $checkoutService = app(CheckoutService::class);
                    $paymentService = app(PaymentService::class);

                    try {
                        // Fetch payment details from CHIP to verify it's actually paid
                        $chipPurchase = $paymentService->getPurchaseStatus($purchaseId);

                        Log::debug('Checkout redirect CHIP purchase lookup', [
                            'purchase_id' => $purchaseId,
                            'chip_status' => $chipPurchase['status'] ?? null,
                            'chip_reference' => $chipPurchase['reference'] ?? null,
                        ]);

                        if ($chipPurchase && $chipPurchase['status'] === 'paid') {
                            $normalizedPurchaseData = array_merge($chipPurchase, [
                                'purchase_id' => $chipPurchase['id'] ?? $purchaseId,
                                'amount' => $paymentIntent['amount'] ?? null,
                                'reference' => $chipPurchase['reference']
                                    ?? ($paymentIntent['customer_data']['reference'] ?? $reference),
                                'gateway' => 'chip',
                                'source' => 'success_callback',
                            ]);

                            Log::debug('Checkout redirect normalized purchase data', [
                                'purchase_id' => $normalizedPurchaseData['purchase_id'],
                                'status' => $normalizedPurchaseData['status'] ?? null,
                                'reference' => $normalizedPurchaseData['reference'] ?? null,
                                'keys' => array_keys($normalizedPurchaseData),
                            ]);

                            // Create order immediately from cart metadata
                            $order = $checkoutService->handlePaymentSuccess($purchaseId, $normalizedPurchaseData);
                            $payment = $order?->payments()->first();

                            Log::info('Order created on redirect (before webhook)', [
                                'purchase_id' => $purchaseId,
                                'order_id' => $order?->id,
                                'source' => 'redirect',
                            ]);
                        }

                        if (! $order) {
                            Log::debug('Checkout redirect did not create order immediately', [
                                'purchase_id' => $purchaseId,
                                'chip_status' => $chipPurchase['status'] ?? null,
                            ]);
                        }
                    } catch (Exception $e) {
                        // If immediate creation fails, webhook will handle it
                        Log::warning('Could not create order on redirect, webhook will handle', [
                            'purchase_id' => $purchaseId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // Fallback: try to find payment by reference
        if (! $payment) {
            $payment = Payment::where('reference', $reference)->latest()->first();
            $order = $payment?->order;
        }

        Log::debug('Checkout success response payload', [
            'reference' => $reference,
            'order_id' => $order?->id,
            'payment_id' => $payment?->id,
        ]);

        return view('checkout.success', [
            'order' => $order ?? null,
            'payment' => $payment ?? null,
            'reference' => $reference,
            'isCompleted' => $payment && $payment->status === 'completed',
            'isPending' => ! $order, // Show "processing" if no order yet
        ]);
    }

    /**
     * Show checkout failure page
     */
    public function failure(Request $request, string $reference): View
    {
        // Get error info from query parameters (if CHIP provides it)
        $error = $request->get('error', 'Masalah teknikal dengan pembayaran');

        $order = null;
        $payment = null;

        // Find cart by reference and get purchase ID from metadata
        $cartData = DB::table('carts')->where('id', $reference)->first();

        if ($cartData && $cartData->metadata) {
            $metadata = json_decode($cartData->metadata, true);
            $paymentIntent = $metadata['payment_intent'] ?? null;

            if ($paymentIntent && isset($paymentIntent['purchase_id'])) {
                $purchaseId = $paymentIntent['purchase_id'];

                // Find payment by CHIP purchase ID
                $payment = Payment::where('gateway_payment_id', $purchaseId)->first();
                $order = $payment?->order;

                // Update payment status if found
                if ($payment && $payment->status === 'pending') {
                    $payment->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                        'note' => $error,
                    ]);
                }
            }
        }

        return view('checkout.failure', [
            'order' => $order,
            'payment' => $payment,
            'error' => $error,
            'reference' => $reference,
        ]);
    }

    /**
     * Show checkout cancel page
     */
    public function cancel(Request $request, string $reference): View
    {
        $order = null;
        $payment = null;

        // Find cart by reference and get purchase ID from metadata
        $cartData = DB::table('carts')->where('id', $reference)->first();

        if ($cartData && $cartData->metadata) {
            $metadata = json_decode($cartData->metadata, true);
            $paymentIntent = $metadata['payment_intent'] ?? null;

            if ($paymentIntent && isset($paymentIntent['purchase_id'])) {
                $purchaseId = $paymentIntent['purchase_id'];

                // Find payment by CHIP purchase ID
                $payment = Payment::where('gateway_payment_id', $purchaseId)->first();
                $order = $payment?->order;

                // Update payment status if found
                if ($payment && $payment->status === 'pending') {
                    $payment->update([
                        'status' => 'cancelled',
                        'failed_at' => now(),
                        'note' => 'Payment cancelled by user',
                    ]);
                }
            }
        }

        return view('checkout.cancel', [
            'order' => $order,
            'payment' => $payment,
            'reference' => $reference,
        ]);
    }
}
