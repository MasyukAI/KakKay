<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

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
        Log::debug('Checkout success redirect hit', ['reference' => $reference]);

        $payload = $this->checkoutService->prepareSuccessView($reference);

        return view('checkout.success', $payload);
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
