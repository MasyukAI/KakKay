<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    /**
     * Show checkout success page
     *
     * Hybrid approach:
     * 1. Try to create order immediately from cart metadata (better UX)
     * 2. If webhook already created it, use existing order
     * 3. Webhook serves as fallback and verification
     */
    public function success(Request $request): View
    {
        // CHIP always includes purchase_id in redirect URL
        $purchaseId = $request->get('purchase_id');
        $orderNumber = $request->get('reference') ?? $request->get('order_number');

        $order = null;
        $payment = null;

        if ($purchaseId) {
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

                    if ($chipPurchase && $chipPurchase['status'] === 'paid') {
                        // Create order immediately from cart metadata
                        $order = $checkoutService->handlePaymentSuccess($purchaseId, $chipPurchase);
                        $payment = $order?->payments()->first();

                        Log::info('Order created on redirect (before webhook)', [
                            'purchase_id' => $purchaseId,
                            'order_id' => $order?->id,
                            'source' => 'redirect',
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
        } elseif ($orderNumber) {
            // Find order by order number
            $order = Order::where('order_number', $orderNumber)->first();
            $payment = $order?->latestPayment();
        }

        return view('checkout.success', [
            'order' => $order ?? null,
            'payment' => $payment ?? null,
            'purchaseId' => $purchaseId ?? null,
            'isCompleted' => $payment && $payment->status === 'completed',
            'isPending' => ! $order, // Show "processing" if no order yet
        ]);
    }

    /**
     * Show checkout failure page
     */
    public function failure(Request $request): View
    {
        // Get error info from query parameters
        $error = $request->get('error', 'Masalah teknikal dengan pembayaran');
        // CHIP always includes purchase_id in redirect URL
        $purchaseId = $request->get('purchase_id');

        $order = null;
        $payment = null;

        if ($purchaseId) {
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

        return view('checkout.failure', [
            'order' => $order,
            'payment' => $payment,
            'error' => $error,
            'purchaseId' => $purchaseId,
        ]);
    }
}
