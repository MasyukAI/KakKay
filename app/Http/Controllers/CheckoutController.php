<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    /**
     * Show checkout success page
     */
    public function success(Request $request): View
    {
        // Get order info from query parameters or session
        $purchaseId = $request->get('purchase_id') ?? session('chip_purchase_id');
        $orderNumber = $request->get('reference') ?? $request->get('order_number');

        $order = null;
        $payment = null;

        if ($purchaseId) {
            // Find payment by CHIP purchase ID
            $payment = Payment::where('gateway_payment_id', $purchaseId)->first();
            $order = $payment?->order;
        } elseif ($orderNumber) {
            // Find order by order number
            $order = Order::where('order_number', $orderNumber)->first();
            $payment = $order?->latestPayment();
        }

        // If we have an order but it's still pending, check if we should update it
        if ($order && $payment && $payment->status === 'pending') {
            // This could be a redirect back from CHIP before webhook is processed
            // We'll keep it as pending until webhook confirms payment
            Log::info('Order accessed via success URL but payment still pending', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'purchase_id' => $purchaseId,
            ]);
        }

        // Clear session data
        session()->forget(['chip_purchase_id', 'checkout_data']);

        return view('checkout.success', [
            'order' => $order ?? null,
            'payment' => $payment ?? null,
            'purchaseId' => $purchaseId ?? null,
            'isCompleted' => $payment && $payment->status === 'completed',
        ]);
    }

    /**
     * Show checkout failure page
     */
    public function failure(Request $request): View
    {
        // Get error info from query parameters
        $error = $request->get('error', 'Masalah teknikal dengan pembayaran');
        $purchaseId = $request->get('purchase_id') ?? session('chip_purchase_id');

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
